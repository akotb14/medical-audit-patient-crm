<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
| Create the disposable database and fill it from the migrations and seeders.
|
| Run it once before the first case, and again whenever the harness stopped
| halfway through:
|
|   php tests/Concurrency/setup.php
|
| The database name comes from the environment and is checked before anything is
| written. Nothing here can reach the database the application itself uses.
*/

require __DIR__.'/lib.php';

conc_boot(false);

$database = conc_database_name();

conc_reset_database();

conc_assert_connected_database();

$users = User::query()->count();

echo 'SETUP:'.json_encode([
    'database' => $database,
    'driver' => DB::connection()->getDriverName(),
    'users' => $users,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
