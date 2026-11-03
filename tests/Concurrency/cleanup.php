<?php

declare(strict_types=1);

/*
| Drop the disposable database the harness used.
|
|   php tests/Concurrency/cleanup.php
|
| The name is checked against the same rules the setup step uses, so the
| application's own database cannot be dropped here even by mistake.
*/

require __DIR__.'/lib.php';

conc_boot(false);

$database = conc_database_name();

$server = conc_server_pdo();

$existed = (int) $server
    ->query('SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = '.$server->quote($database))
    ->fetchColumn() === 1;

$server->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $database));

echo 'CLEANUP:'.json_encode([
    'database' => $database,
    'dropped' => $existed,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
