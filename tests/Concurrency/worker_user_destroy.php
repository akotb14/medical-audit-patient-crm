<?php

declare(strict_types=1);

/*
| The `actor` that deletes a user through the real controller action, used for
| the last-administrator race: two administrators deleting each other at once.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\UserController;
use App\Models\User;

conc_boot();

$config = conc_config();
$userId = (int) $config['user_id'];
$actingUserId = (int) $config['acting_user_id'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'user_destroy', 'ok' => false, 'error' => 'signal timeout']);
}

$user = User::query()->findOrFail($userId);
$request = conc_plain_request('DELETE', "/users/{$userId}", []);

$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(UserController::class)->destroy($user);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionElapsed = (int) round((microtime(true) - $actionStart) * 1000);

$fresh = User::query()->find($userId);

conc_finish([
    'role' => 'actor',
    'action' => 'user_destroy',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'action_elapsed_ms' => $actionElapsed,
    'target_exists' => $fresh !== null,
    'actor_still_exists' => User::query()->find($actingUserId) !== null,
]);
