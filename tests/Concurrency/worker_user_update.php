<?php

declare(strict_types=1);

/*
| The `actor` that updates a user through the real controller action, used for
| the last-administrator race: two administrators demoting each other at once.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\UserController;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

conc_boot();

$config = conc_config();
$userId = (int) $config['user_id'];
$actingUserId = (int) $config['acting_user_id'];
$roleId = (int) $config['role_id'];
$waitFor = (string) ($config['wait_for'] ?? '');

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'user_update', 'ok' => false, 'error' => 'signal timeout']);
}

$user = User::query()->findOrFail($userId);

$payload = [
    'name' => $user->name,
    'username' => $user->username,
    'role_id' => $roleId,
];

$validationStart = microtime(true);
$request = conc_form_request(
    UpdateUserRequest::class,
    'PUT',
    "/users/{$userId}",
    $payload,
    ['user' => $user],
);
$validationElapsed = (int) round((microtime(true) - $validationStart) * 1000);

$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(UserController::class)->update($request, $user);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
}

$actionElapsed = (int) round((microtime(true) - $actionStart) * 1000);

$fresh = User::query()->find($userId);

conc_finish([
    'role' => 'actor',
    'action' => 'user_update',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'validation_elapsed_ms' => $validationElapsed,
    'action_elapsed_ms' => $actionElapsed,
    'target_roles' => $fresh?->getRoleNames()->values()->all(),
]);
