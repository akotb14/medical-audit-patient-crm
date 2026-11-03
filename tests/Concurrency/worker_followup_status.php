<?php

declare(strict_types=1);

/*
| The `actor` that changes a follow-up status through the real controller action.
| It reports both what the row ended up holding and what the action told the
| user, so a case can tell a refused transition from a successful one.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\FollowUpsController;
use App\Http\Requests\FollowUpsRequests\UpdateFollowUpStatusRequest;
use App\Models\FollowUp;

conc_boot();

$config = conc_config();
$followUpId = (int) $config['followup_id'];
$actingUserId = (int) $config['acting_user_id'];
$requestedStatus = (string) $config['status'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'followup_status', 'ok' => false, 'error' => 'signal timeout']);
}

$followUp = FollowUp::query()->findOrFail($followUpId);
$request = conc_form_request(UpdateFollowUpStatusRequest::class, 'PATCH', "/follow_ups/{$followUpId}/status", [
    'status' => $requestedStatus,
]);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];
$flashError = null;
$flashSuccess = null;

try {
    $response = app(FollowUpsController::class)->status($request, $followUp);
    $flashError = $response->getSession()?->get('error');
    $flashSuccess = $response->getSession()?->get('success');
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$fresh = FollowUp::query()->findOrFail($followUpId);

conc_finish([
    'role' => 'actor',
    'action' => 'followup_status',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'flash_error' => $flashError,
    'flash_success' => $flashSuccess,
    'requested_status' => $requestedStatus,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'status' => $fresh->status,
    'completed_at' => $fresh->completed_at?->toDateTimeString(),
    'updated_by' => $fresh->updated_by,
]);
