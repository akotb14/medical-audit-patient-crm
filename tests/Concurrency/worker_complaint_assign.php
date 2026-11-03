<?php

declare(strict_types=1);

/*
| The `actor` that assigns a complaint through the real controller action.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\ComplaintController;
use App\Http\Requests\ComplaintRequests\AssignComplaintRequest;
use App\Models\Complaint;

conc_boot();

$config = conc_config();
$complaintId = (int) $config['complaint_id'];
$actingUserId = (int) $config['acting_user_id'];
$assigneeId = (int) $config['assigned_to'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'complaint_assign', 'ok' => false, 'error' => 'signal timeout']);
}

$complaint = Complaint::query()->findOrFail($complaintId);
$request = conc_form_request(AssignComplaintRequest::class, 'PATCH', "/complaints/{$complaintId}/assign", [
    'assigned_to' => $assigneeId,
]);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(ComplaintController::class)->assign($request, $complaint);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$fresh = Complaint::query()->findOrFail($complaintId);

conc_finish([
    'role' => 'actor',
    'action' => 'complaint_assign',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'status' => $fresh->status,
    'assigned_to' => $fresh->assigned_to,
]);
