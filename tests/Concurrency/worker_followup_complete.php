<?php

declare(strict_types=1);

/*
| The `actor` that completes a follow-up through the real controller action.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\FollowUpsController;
use App\Http\Requests\FollowUpsRequests\CompleteFollowUpRequest;
use App\Models\FollowUp;

conc_boot();

$config = conc_config();
$followUpId = (int) $config['followup_id'];
$actingUserId = (int) $config['acting_user_id'];
$outcomeNote = (string) $config['outcome_note'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'followup_complete', 'ok' => false, 'error' => 'signal timeout']);
}

$followUp = FollowUp::query()->findOrFail($followUpId);
$request = conc_form_request(CompleteFollowUpRequest::class, 'PATCH', "/follow_ups/{$followUpId}/complete", [
    'outcome_note' => $outcomeNote,
]);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(FollowUpsController::class)->complete($request, $followUp);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$fresh = FollowUp::query()->findOrFail($followUpId);

conc_finish([
    'role' => 'actor',
    'action' => 'followup_complete',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'status' => $fresh->status,
    'outcome_note' => $fresh->outcome_note,
    'completed_at' => $fresh->completed_at?->toDateTimeString(),
]);
