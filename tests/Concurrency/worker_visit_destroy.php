<?php

declare(strict_types=1);

/*
| The `actor` that deletes a visit through the real controller action.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\VisitController;
use App\Models\Visit;

conc_boot();

$config = conc_config();
$visitId = (int) $config['visit_id'];
$actingUserId = (int) $config['acting_user_id'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'visit_destroy', 'ok' => false, 'error' => 'signal timeout']);
}

$visit = Visit::query()->findOrFail($visitId);
$request = conc_plain_request('DELETE', "/visits/{$visitId}", []);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(VisitController::class)->destroy($visit);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$fresh = Visit::query()->find($visitId);

conc_finish([
    'role' => 'actor',
    'action' => 'visit_destroy',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'visit_exists' => $fresh !== null,
    'status' => $fresh?->visit_status,
]);
