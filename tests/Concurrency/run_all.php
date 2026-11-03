<?php

declare(strict_types=1);

/*
| MySQL concurrency harness.
|
| Each case starts at least two real OS processes. A `holder` worker takes the
| row lock inside a transaction, announces the lock through a signal file and
| keeps the transaction open for a measured window; the `actor` worker then runs
| the real controller action and must block until the holder commits. Cases that
| need no artificial window use a barrier file so both actors start together.
|
| Every case runs against a disposable MySQL database that this file rebuilds
| first, so a run always starts from the same state and can be repeated.
*/

require __DIR__.'/lib.php';

use App\Enums\PermissionEnum;
use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

conc_boot(false);

// A repeatable run: the schema is rebuilt on the database the environment names,
// which is verified again immediately before it is dropped.
conc_reset_database();
conc_assert_connected_database();
conc_forget_permission_cache();

$admin = User::query()->where('username', 'admin@clinic')->firstOrFail();

if (! $admin->hasRole('Admin')) {
    fwrite(STDERR, "the seeded administrator does not hold the admin role\n");
    exit(96);
}

$workDir = sys_get_temp_dir().'/conc_run_'.getmypid();
@mkdir($workDir, 0777, true);

$startedAt = microtime(true);

/**
 * @return array{proc: resource, pipes: array<int, resource>, worker: string}
 */
function run_spawn(string $worker, array $config): array
{
    $command = [PHP_BINARY, __DIR__.'/'.$worker, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];

    $process = proc_open($command, [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, conc_root());

    if (! is_resource($process)) {
        throw new RuntimeException('could not start '.$worker);
    }

    return ['proc' => $process, 'pipes' => $pipes, 'worker' => $worker];
}

/**
 * @param  array{proc: resource, pipes: array<int, resource>, worker: string}  $handle
 * @return array<string, mixed>
 */
function run_finish(array $handle): array
{
    $stdout = stream_get_contents($handle['pipes'][1]);
    $stderr = stream_get_contents($handle['pipes'][2]);

    fclose($handle['pipes'][1]);
    fclose($handle['pipes'][2]);

    $exitCode = proc_close($handle['proc']);

    $result = null;

    foreach (explode("\n", (string) $stdout) as $line) {
        if (str_starts_with($line, 'RESULT:')) {
            $result = json_decode(substr($line, 7), true);
        }
    }

    return [
        'worker' => $handle['worker'],
        'exit' => $exitCode,
        'result' => $result,
        'stdout' => trim((string) $stdout),
        'stderr' => trim((string) $stderr),
    ];
}

function run_case_dir(string $workDir, string $name): string
{
    $dir = $workDir.'/'.$name;
    @mkdir($dir, 0777, true);

    foreach (glob($dir.'/*') ?: [] as $file) {
        @unlink($file);
    }

    return $dir;
}

function run_make_visit(User $admin): Visit
{
    $patient = Patient::factory()->create(['created_by' => $admin->id]);
    $doctor = Doctor::factory()->create([
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    return Visit::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'visit_status' => 'created',
        'date' => now()->addDays(3)->startOfHour(),
        'reason' => 'سبب الزيارة الأصلي قبل التحديث المتزامن',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);
}

function run_make_followup(User $admin): FollowUp
{
    $visit = Visit::factory()->completed()->create([
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    return FollowUp::factory()->forVisit($visit)->create([
        'status' => 'scheduled',
        'outcome_note' => null,
        'completed_at' => null,
        'due_date' => now()->addDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);
}

function run_holder_then_actor(string $workDir, string $name, array $holderConfig, string $actorWorker, array $actorConfig): array
{
    $dir = run_case_dir($workDir, $name);
    $signal = $dir.'/holder.locked';

    $holderConfig['signal'] = $signal;

    $holder = run_spawn('worker_hold.php', $holderConfig);

    if (! conc_wait_for($signal, 20.0)) {
        return [
            'holder' => run_finish($holder),
            'actor' => null,
            'error' => 'the holder never announced its lock',
        ];
    }

    $actorConfig['wait_for'] = $signal;
    $actor = run_spawn($actorWorker, $actorConfig);

    return [
        'holder' => run_finish($holder),
        'actor' => run_finish($actor),
    ];
}

function run_together(string $workDir, string $name, string $workerA, array $configA, string $workerB, array $configB): array
{
    $dir = run_case_dir($workDir, $name);
    $barrier = $dir.'/go';
    $readyA = $dir.'/ready_a';
    $readyB = $dir.'/ready_b';

    $configA['ready'] = $readyA;
    $configA['wait_for'] = $barrier;
    $configB['ready'] = $readyB;
    $configB['wait_for'] = $barrier;

    $a = run_spawn($workerA, $configA);
    $b = run_spawn($workerB, $configB);

    $ready = conc_wait_for($readyA, 20.0) && conc_wait_for($readyB, 20.0);

    // Released either way, so a process that failed to boot cannot hang the run.
    conc_signal($barrier);

    return [
        'both_ready_before_barrier' => $ready,
        'a' => run_finish($a),
        'b' => run_finish($b),
    ];
}

/**
 * True when the actor action was already running while the holder still held
 * the row lock and finished only after that lock was released.
 */
function run_was_blocked_by_holder(array $case): bool
{
    $holder = $case['holder']['result'] ?? null;
    $actor = $case['actor']['result'] ?? null;

    if (! is_array($holder) || ! is_array($actor)) {
        return false;
    }

    $commitAt = $holder['commit_at'] ?? null;
    $start = $actor['action_started_at'] ?? null;
    $end = $actor['action_ended_at'] ?? null;

    if ($commitAt === null || $start === null || $end === null) {
        return false;
    }

    return $start < $commitAt
        && $end >= $commitAt
        && ($end - $start) >= 0.3;
}

function run_report(string $name, array $payload): void
{
    echo 'SCENARIO:'.json_encode(['name' => $name, ...$payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
}

$results = [];

/* ---------------------------------------------------------------------
 | 1. Visit: a date/fields update racing a completion.
 * ------------------------------------------------------------------- */
$visit = run_make_visit($admin);
$originalDate = $visit->date->toDateTimeString();
$originalReason = $visit->reason;

$case = run_holder_then_actor($workDir, 'visit_update_vs_complete', [
    'entity' => 'visit_complete',
    'id' => $visit->id,
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
], 'worker_visit_update.php', [
    'visit_id' => $visit->id,
    'acting_user_id' => $admin->id,
]);

$fresh = $visit->fresh();
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'visit_update_vs_complete',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => [
        'status' => $fresh->visit_status,
        'reason' => $fresh->reason,
        'date' => $fresh->date?->toDateTimeString(),
    ],
    'checks' => [
        'actor_was_validation_error' => ($actorResult['outcome'] ?? null) === 'validation_error',
        'actor_saw_locked_visit_message' => isset($actorResult['errors']['visit']),
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'actor_did_not_write_reason' => $fresh->reason === $originalReason,
        'actor_did_not_write_date' => $fresh->date?->toDateTimeString() === $originalDate,
        'visit_is_completed' => $fresh->visit_status === 'completed',
        'handler_message' => $actorResult['errors']['visit'][0] ?? null,
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

$visit = run_make_visit($admin);
$originalDate = $visit->date->toDateTimeString();
$originalReason = $visit->reason;

$case = run_holder_then_actor($workDir, 'visit_update_vs_cancel', [
    'entity' => 'visit_cancel',
    'id' => $visit->id,
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
], 'worker_visit_update.php', [
    'visit_id' => $visit->id,
    'acting_user_id' => $admin->id,
]);

$fresh = $visit->fresh();
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'visit_update_vs_cancel',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => [
        'status' => $fresh->visit_status,
        'reason' => $fresh->reason,
        'date' => $fresh->date?->toDateTimeString(),
    ],
    'checks' => [
        'actor_was_validation_error' => ($actorResult['outcome'] ?? null) === 'validation_error',
        'actor_saw_locked_visit_message' => isset($actorResult['errors']['visit']),
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'actor_did_not_write_reason' => $fresh->reason === $originalReason,
        'actor_did_not_write_date' => $fresh->date?->toDateTimeString() === $originalDate,
        'visit_is_cancelled' => $fresh->visit_status === 'cancelled',
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 2. Visit: a delete racing a completion. A completed visit is history.
 * ------------------------------------------------------------------- */
$visit = run_make_visit($admin);

$case = run_holder_then_actor($workDir, 'visit_destroy_vs_complete', [
    'entity' => 'visit_complete',
    'id' => $visit->id,
    'hold_ms' => 2200,
    'updated_by' => $admin->id,
], 'worker_visit_destroy.php', [
    'visit_id' => $visit->id,
    'acting_user_id' => $admin->id,
]);

$fresh = Visit::query()->find($visit->id);
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'visit_destroy_vs_complete',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => ['status' => $fresh?->visit_status, 'exists' => $fresh !== null],
    'checks' => [
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'visit_still_exists' => $fresh !== null,
        'visit_is_completed' => $fresh?->visit_status === 'completed',
        'actor_returned_no_exception' => ($actorResult['outcome'] ?? null) === 'response',
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 3. Follow-up: two completions, the second one must lose.
 * ------------------------------------------------------------------- */
$followUp = run_make_followup($admin);

$case = run_holder_then_actor($workDir, 'followup_complete_race', [
    'entity' => 'followup_done',
    'id' => $followUp->id,
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
    'outcome_note' => 'النتيجة الأولى من العملية الحاجزة',
], 'worker_followup_complete.php', [
    'followup_id' => $followUp->id,
    'acting_user_id' => $admin->id,
    'outcome_note' => 'النتيجة الثانية من العملية المنتظرة',
]);

$fresh = $followUp->fresh();
$holderResult = $case['holder']['result']['state'] ?? [];
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'followup_complete_race',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => [
        'status' => $fresh->status,
        'outcome_note' => $fresh->outcome_note,
        'completed_at' => $fresh->completed_at?->toDateTimeString(),
    ],
    'checks' => [
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'status_is_done' => $fresh->status === 'done',
        'outcome_is_the_holder_one' => $fresh->outcome_note === 'النتيجة الأولى من العملية الحاجزة',
        'holder_outcome_preserved' => $fresh->outcome_note === ($holderResult['outcome_note'] ?? null),
        'completed_at_matches_holder' => $fresh->completed_at?->toDateTimeString() === ($holderResult['completed_at'] ?? null),
        'completed_at_is_not_null' => $fresh->completed_at !== null,
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

$followUp = run_make_followup($admin);
$case = run_together(
    $workDir,
    'followup_complete_together',
    'worker_followup_complete.php',
    ['followup_id' => $followUp->id, 'acting_user_id' => $admin->id, 'outcome_note' => 'نتيجة العملية أ'],
    'worker_followup_complete.php',
    ['followup_id' => $followUp->id, 'acting_user_id' => $admin->id, 'outcome_note' => 'نتيجة العملية ب'],
);

$fresh = $followUp->fresh();
$noteA = $case['a']['result']['outcome_note'] ?? null;
$noteB = $case['b']['result']['outcome_note'] ?? null;

$results[] = [
    'name' => 'followup_complete_together',
    'case' => $case,
    'final' => [
        'status' => $fresh->status,
        'outcome_note' => $fresh->outcome_note,
        'completed_at' => $fresh->completed_at?->toDateTimeString(),
    ],
    'checks' => [
        'both_processes_started_together' => (bool) $case['both_ready_before_barrier'],
        'status_is_done' => $fresh->status === 'done',
        'completed_at_is_not_null' => $fresh->completed_at !== null,
        'both_processes_observed_one_consistent_row' => $noteA === $noteB && $noteA === $fresh->outcome_note,
        'stored_note_is_one_of_the_two' => in_array($fresh->outcome_note, ['نتيجة العملية أ', 'نتيجة العملية ب'], true),
    ],
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 4. Follow-up: a status change racing a completion that already closed the
 |    row. `done` is the end of the workflow, so the status change has to be
 |    refused and must not clear the completion moment on its way out.
 * ------------------------------------------------------------------- */
$followUp = run_make_followup($admin);

$case = run_holder_then_actor($workDir, 'followup_status_vs_complete', [
    'entity' => 'followup_done',
    'id' => $followUp->id,
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
    'outcome_note' => 'نتيجة الإنجاز الذي حجز الصف',
], 'worker_followup_status.php', [
    'followup_id' => $followUp->id,
    'acting_user_id' => $admin->id,
    'status' => 'cancelled',
]);

$fresh = $followUp->fresh();
$holderState = $case['holder']['result']['state'] ?? [];
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'followup_status_vs_complete',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => [
        'status' => $fresh->status,
        'outcome_note' => $fresh->outcome_note,
        'completed_at' => $fresh->completed_at?->toDateTimeString(),
    ],
    'checks' => [
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'status_is_still_done' => $fresh->status === 'done',
        'outcome_note_untouched' => $fresh->outcome_note === ($holderState['outcome_note'] ?? null),
        'completed_at_untouched' => $fresh->completed_at?->toDateTimeString() === ($holderState['completed_at'] ?? null),
        'completed_at_is_not_null' => $fresh->completed_at !== null,
        'actor_was_told_the_status_is_closed' => ($actorResult['flash_error'] ?? null) === 'لا يمكن تغيير حالة المتابعة',
        'actor_reported_no_success' => ($actorResult['flash_success'] ?? null) === null,
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 5. Follow-up: two status changes released together on the same row. Only
 |    one may move it, and the row has to describe exactly that one: `done`
 |    carries a completion moment, `missed` does not.
 * ------------------------------------------------------------------- */
/*
| A second operator, holding only the permission the status action checks. It is
| deliberately not an administrator: an extra administrator would change what
| the last-administrator cases below are counting.
*/
$secondOperator = User::factory()->create();
$secondOperator->givePermissionTo(PermissionEnum::FOLLOWUPS_STATUS->value);

$followUp = run_make_followup($admin);

$case = run_together(
    $workDir,
    'followup_status_together',
    'worker_followup_status.php',
    ['followup_id' => $followUp->id, 'acting_user_id' => $admin->id, 'status' => 'missed'],
    'worker_followup_status.php',
    ['followup_id' => $followUp->id, 'acting_user_id' => $secondOperator->id, 'status' => 'done'],
);

$fresh = $followUp->fresh();
$successA = ($case['a']['result']['flash_success'] ?? null) !== null;
$successB = ($case['b']['result']['flash_success'] ?? null) !== null;
$winnerId = $fresh->status === 'done' ? $secondOperator->id : $admin->id;

$results[] = [
    'name' => 'followup_status_together',
    'case' => $case,
    'final' => [
        'status' => $fresh->status,
        'completed_at' => $fresh->completed_at?->toDateTimeString(),
        'updated_by' => $fresh->updated_by,
    ],
    'checks' => [
        'both_processes_started_together' => (bool) $case['both_ready_before_barrier'],
        'final_status_is_one_of_the_two' => in_array($fresh->status, ['missed', 'done'], true),
        'exactly_one_transition_succeeded' => $successA !== $successB,
        'the_row_names_the_one_that_succeeded' => ($fresh->status === 'missed') === $successA,
        'stored_by_is_the_winner' => (int) $fresh->updated_by === (int) $winnerId,
        'completion_moment_matches_the_status' => ($fresh->status === 'done') === ($fresh->completed_at !== null),
        'the_loser_was_told_why' => ($successA ? ($case['b']['result']['flash_error'] ?? null) : ($case['a']['result']['flash_error'] ?? null)) !== null,
    ],
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 6. Complaint: an assignment racing another assignment.
 * ------------------------------------------------------------------- */
$assigneeA = User::factory()->create();
$assigneeB = User::factory()->create();
$complaint = Complaint::factory()->unassigned()->create([
    'created_by' => $admin->id,
    'updated_by' => $admin->id,
]);

$case = run_holder_then_actor($workDir, 'complaint_assign_race', [
    'entity' => 'complaint_assigned',
    'id' => $complaint->id,
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
    'assigned_to' => $assigneeA->id,
], 'worker_complaint_assign.php', [
    'complaint_id' => $complaint->id,
    'acting_user_id' => $admin->id,
    'assigned_to' => $assigneeB->id,
]);

$fresh = $complaint->fresh();
$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);

$results[] = [
    'name' => 'complaint_assign_race',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => ['status' => $fresh->status, 'assigned_to' => $fresh->assigned_to],
    'checks' => [
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'actor_rejected_by_post_lock_status' => ($actorResult['outcome'] ?? null) === 'exception'
            && isset($actorResult['errors']['status']),
        'status_is_assigned' => $fresh->status === 'assigned',
        'assignee_is_the_holder_one' => (int) $fresh->assigned_to === $assigneeA->id,
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

$complaint = Complaint::factory()->unassigned()->create([
    'created_by' => $admin->id,
    'updated_by' => $admin->id,
]);

$case = run_together(
    $workDir,
    'complaint_assign_together',
    'worker_complaint_assign.php',
    ['complaint_id' => $complaint->id, 'acting_user_id' => $admin->id, 'assigned_to' => $assigneeA->id],
    'worker_complaint_assign.php',
    ['complaint_id' => $complaint->id, 'acting_user_id' => $admin->id, 'assigned_to' => $assigneeB->id],
);

$fresh = $complaint->fresh();
$outcomes = [$case['a']['result']['outcome'] ?? null, $case['b']['result']['outcome'] ?? null];

$results[] = [
    'name' => 'complaint_assign_together',
    'case' => $case,
    'final' => ['status' => $fresh->status, 'assigned_to' => $fresh->assigned_to],
    'checks' => [
        'both_processes_started_together' => (bool) $case['both_ready_before_barrier'],
        'exactly_one_assignment_succeeded' => count(array_filter($outcomes, static fn ($o) => $o === 'exception')) === 1,
        'status_is_assigned' => $fresh->status === 'assigned',
        'assignee_is_one_of_the_two' => in_array((int) $fresh->assigned_to, [$assigneeA->id, $assigneeB->id], true),
    ],
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 7. Last administrator: concurrent deletion of two different admins.
 * ------------------------------------------------------------------- */
$seededHadAdminRole = $admin->hasRole('Admin');

if ($seededHadAdminRole) {
    $admin->removeRole('Admin');
}

$adminA = User::factory()->create();
$adminB = User::factory()->create();
$adminA->assignRole('Admin');
$adminB->assignRole('Admin');

$adminsBefore = User::role('Admin')->count();

$case = run_together(
    $workDir,
    'last_admin_delete_together',
    'worker_user_destroy.php',
    ['user_id' => $adminB->id, 'acting_user_id' => $adminA->id],
    'worker_user_destroy.php',
    ['user_id' => $adminA->id, 'acting_user_id' => $adminB->id],
);

$adminsAfter = User::role('Admin')->count();
$survivorA = User::query()->find($adminA->id);
$survivorB = User::query()->find($adminB->id);

$results[] = [
    'name' => 'last_admin_delete_together',
    'case' => $case,
    'final' => [
        'admins_before' => $adminsBefore,
        'admins_after' => $adminsAfter,
        'admin_a_exists' => $survivorA !== null,
        'admin_b_exists' => $survivorB !== null,
        'admin_a_roles' => $survivorA?->getRoleNames()->values()->all(),
        'admin_b_roles' => $survivorB?->getRoleNames()->values()->all(),
    ],
    'checks' => [
        'both_processes_started_together' => (bool) $case['both_ready_before_barrier'],
        'exactly_one_admin_remains' => $adminsAfter === 1,
        'at_least_one_admin_remains' => $adminsAfter >= 1,
        'exactly_one_of_the_two_was_deleted' => ($survivorA === null) !== ($survivorB === null),
        'survivor_still_holds_admin' => (bool) ($survivorA?->hasRole('Admin') || $survivorB?->hasRole('Admin')),
    ],
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

/* ---------------------------------------------------------------------
 | 8. Last administrator: concurrent demotion of two different admins.
 * ------------------------------------------------------------------- */
$survivingAdmin = User::role('Admin')->firstOrFail();
$receptionistRole = Role::findByName('Receptionist');
$adminC = User::factory()->create();
$adminC->assignRole('Admin');

$adminsBefore = User::role('Admin')->count();

$case = run_together(
    $workDir,
    'last_admin_demote_together',
    'worker_user_update.php',
    ['user_id' => $adminC->id, 'acting_user_id' => $survivingAdmin->id, 'role_id' => $receptionistRole->id],
    'worker_user_update.php',
    ['user_id' => $survivingAdmin->id, 'acting_user_id' => $adminC->id, 'role_id' => $receptionistRole->id],
);

$adminsAfter = User::role('Admin')->count();
$targetA = User::query()->find($survivingAdmin->id);
$targetC = User::query()->find($adminC->id);

$results[] = [
    'name' => 'last_admin_demote_together',
    'case' => $case,
    'final' => [
        'admins_before' => $adminsBefore,
        'admins_after' => $adminsAfter,
        'target_a_roles' => $targetA?->getRoleNames()->values()->all(),
        'target_c_roles' => $targetC?->getRoleNames()->values()->all(),
    ],
    'checks' => [
        'both_processes_started_together' => (bool) $case['both_ready_before_barrier'],
        'exactly_one_admin_remains' => $adminsAfter === 1,
        'at_least_one_admin_remains' => $adminsAfter >= 1,
        'exactly_one_demotion_succeeded' => ($targetA?->hasRole('Admin') ?? false) !== ($targetC?->hasRole('Admin') ?? false),
    ],
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

if ($seededHadAdminRole) {
    $admin->assignRole('Admin');
}

/* ---------------------------------------------------------------------
 | 9. MRN: a registration racing an in-flight insert of the generated MRN.
 * ------------------------------------------------------------------- */
$pinnedMrn = 'MRN-AAAAAAAA';
$holderPhone = '01000000901';
$actorPhone = '01000000902';

$case = run_holder_then_actor($workDir, 'mrn_collision_race', [
    'entity' => 'patient_insert',
    'hold_ms' => 2500,
    'updated_by' => $admin->id,
    'mrn' => $pinnedMrn,
    'phone' => $holderPhone,
    'fullname' => 'مريض العملية الحاجزة',
], 'worker_patient_store.php', [
    'acting_user_id' => $admin->id,
    'phone' => $actorPhone,
    'fullname' => 'مريض العملية المنتظرة',
]);

$actorResult = $case['actor']['result'] ?? [];
$actorElapsed = (int) ($actorResult['action_elapsed_ms'] ?? 0);
$actorPatient = Patient::query()->where('phone', $actorPhone)->first();
$holderPatient = Patient::query()->where('phone', $holderPhone)->first();
$duplicates = DB::table('patients')->select('mrn')->groupBy('mrn')->havingRaw('COUNT(*) > 1')->count();

$results[] = [
    'name' => 'mrn_collision_race',
    'holder' => $case['holder'],
    'actor' => $case['actor'],
    'final' => [
        'actor_mrn' => $actorPatient?->mrn,
        'holder_mrn' => $holderPatient?->mrn,
        'patients' => Patient::withTrashed()->count(),
        'duplicate_mrn_groups' => $duplicates,
    ],
    'checks' => [
        'actor_blocked_while_holder_held' => run_was_blocked_by_holder($case),
        'actor_registration_succeeded' => $actorPatient !== null,
        'holder_registration_committed' => $holderPatient !== null,
        'actor_retried_with_a_different_mrn' => $actorPatient !== null && $actorPatient->mrn !== $pinnedMrn,
        'pinned_mrn_was_taken_once' => Patient::withTrashed()->where('mrn', $pinnedMrn)->count() === 1,
        'no_duplicate_mrns_persisted' => $duplicates === 0,
    ],
    'actor_elapsed_ms' => $actorElapsed,
];
run_report($results[array_key_last($results)]['name'], $results[array_key_last($results)]);

$allChecks = [];

foreach ($results as $result) {
    foreach ($result['checks'] ?? [] as $check => $passed) {
        if (is_bool($passed)) {
            $allChecks[$result['name'].'.'.$check] = $passed;
        }
    }
}

$failed = array_keys(array_filter($allChecks, static fn (bool $passed) => ! $passed));

echo 'SUMMARY:'.json_encode([
    'scenarios' => count($results),
    'checks' => count($allChecks),
    'failed_checks' => $failed,
    'all_passed' => $failed === [],
    'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
    'work_dir' => $workDir,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
