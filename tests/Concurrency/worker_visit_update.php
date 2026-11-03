<?php

declare(strict_types=1);

/*
| The `actor` that edits a visit through the real controller action, with the
| request resolved the way the router resolves it: the FormRequest is built and
| validated before the action runs, exactly as it would be inside a request.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\VisitController;
use App\Http\Requests\VistRequests\UpdateVisitRequest;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

conc_boot();

$config = conc_config();
$visitId = (int) $config['visit_id'];
$actingUserId = (int) $config['acting_user_id'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'visit_update', 'ok' => false, 'error' => 'signal timeout']);
}

$visit = Visit::query()->findOrFail($visitId);
$originalDate = $visit->date?->toDateTimeString();

$payload = [
    'patient_id' => $visit->patient_id,
    'doctor_id' => $visit->doctor_id,
    'reason' => 'سبب الزيارة المعدل أثناء اكتمال الزيارة',
    'date' => now()->addDays(7)->toDateString(),
];

$validationStart = microtime(true);
$request = conc_form_request(
    UpdateVisitRequest::class,
    'PUT',
    "/visits/{$visitId}",
    $payload,
    ['visit' => $visit],
);
$validationElapsed = (int) round((microtime(true) - $validationStart) * 1000);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(VisitController::class)->update($visit, $request);
} catch (ValidationException $exception) {
    $outcome = 'validation_error';
    $errors = $exception->errors();
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$fresh = Visit::query()->findOrFail($visitId);

conc_finish([
    'role' => 'actor',
    'action' => 'visit_update',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'validation_elapsed_ms' => $validationElapsed,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'status' => $fresh->visit_status,
    'reason' => $fresh->reason,
    'date' => $fresh->date?->toDateTimeString(),
    'original_date' => $originalDate,
]);
