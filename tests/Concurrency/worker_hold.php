<?php

declare(strict_types=1);

/*
| The `holder` of a case: it takes the row lock inside a real transaction,
| announces the lock through a signal file and keeps the transaction open for a
| measured window before writing and committing. The competing action then has a
| lock to block on.
*/

require __DIR__.'/lib.php';

use App\Models\Complaint;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

conc_boot();

$config = conc_config();
$entity = (string) ($config['entity'] ?? '');
$id = isset($config['id']) ? (int) $config['id'] : null;
$holdMs = (int) ($config['hold_ms'] ?? 2500);
$signal = (string) ($config['signal'] ?? '');
$updatedBy = (int) ($config['updated_by'] ?? 1);

$startedAt = microtime(true);
$signalAt = null;

try {
    $state = DB::transaction(function () use ($entity, $id, $holdMs, $signal, $updatedBy, $config, &$signalAt): array {
        if ($entity === 'patient_insert') {
            $patient = Patient::create([
                'fullname' => (string) ($config['fullname'] ?? 'مريض تحقق التزامن'),
                'phone' => (string) $config['phone'],
                'gender' => 'male',
                'birthdate' => '1990-01-01',
                'blood_group' => 'O+',
                'status' => 'active',
                'mrn' => (string) $config['mrn'],
                'created_by' => $updatedBy,
            ]);

            conc_signal($signal);
            $signalAt = microtime(true);
            usleep($holdMs * 1000);

            return ['patient_id' => $patient->id, 'mrn' => $patient->mrn];
        }

        if ($entity === 'visit_complete' || $entity === 'visit_cancel') {
            $visit = Visit::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            conc_signal($signal);
            $signalAt = microtime(true);
            usleep($holdMs * 1000);

            $visit->update([
                'visit_status' => $entity === 'visit_cancel' ? 'cancelled' : 'completed',
                'updated_by' => $updatedBy,
            ]);

            return ['status' => (string) Visit::query()->whereKey($id)->value('visit_status')];
        }

        if ($entity === 'followup_done') {
            $followUp = FollowUp::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            conc_signal($signal);
            $signalAt = microtime(true);
            usleep($holdMs * 1000);

            $followUp->update([
                'status' => 'done',
                'outcome_note' => (string) $config['outcome_note'],
                'completed_at' => now(),
                'updated_by' => $updatedBy,
            ]);

            $fresh = FollowUp::query()->whereKey($id)->firstOrFail();

            return [
                'status' => $fresh->status,
                'outcome_note' => $fresh->outcome_note,
                'completed_at' => $fresh->completed_at?->toDateTimeString(),
            ];
        }

        if ($entity === 'complaint_assigned') {
            $complaint = Complaint::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            conc_signal($signal);
            $signalAt = microtime(true);
            usleep($holdMs * 1000);

            $complaint->update([
                'status' => 'assigned',
                'assigned_to' => (int) $config['assigned_to'],
                'updated_by' => $updatedBy,
            ]);

            $fresh = Complaint::query()->whereKey($id)->firstOrFail();

            return ['status' => $fresh->status, 'assigned_to' => $fresh->assigned_to];
        }

        throw new RuntimeException('unknown entity: '.$entity);
    });

    $commitAt = microtime(true);

    conc_finish([
        'role' => 'holder',
        'entity' => $entity,
        'ok' => true,
        'state' => $state,
        'signal_at' => $signalAt,
        'commit_at' => $commitAt,
        'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
    ]);
} catch (Throwable $exception) {
    conc_finish([
        'role' => 'holder',
        'entity' => $entity,
        'ok' => false,
        'error' => get_class($exception).': '.$exception->getMessage(),
    ]);
}
