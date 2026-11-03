<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/*
| Shared bootstrap for the MySQL concurrency harness.
|
| The harness only ever runs against a disposable MySQL database whose name is
| named explicitly in the environment, and every process re-checks that name
| against the live connection before it reads or writes anything. There is no
| fallback: an unset variable, a name that is not marked disposable, or the
| application's own database name all abort the process instead of running.
*/

/** The suffix every disposable database name has to carry. */
const CONC_DATABASE_SUFFIX = '_verify';

/** The environment variable holding the disposable database name. */
const CONC_DATABASE_ENV = 'CONCURRENCY_DB_DATABASE';

function conc_root(): string
{
    return dirname(__DIR__, 2);
}

function conc_abort(string $message): never
{
    fwrite(STDERR, 'ABORT: '.$message."\n");

    exit(99);
}

/**
 * The database the application itself is configured against, read straight from
 * `.env` so that the value is known before the framework loads it.
 */
function conc_application_database(): ?string
{
    $path = conc_root().'/.env';

    if (! is_readable($path)) {
        return null;
    }

    foreach (file($path) ?: [] as $line) {
        if (preg_match('/^\s*DB_DATABASE\s*=\s*(.+)$/', $line, $matches) === 1) {
            return trim(trim($matches[1]), "\"'");
        }
    }

    return null;
}

/**
 * The disposable database name, checked against every rule the harness relies
 * on. Nothing is cached across processes, so each worker enforces them itself.
 */
function conc_database_name(): string
{
    $configured = getenv(CONC_DATABASE_ENV);
    $configured = is_string($configured) ? trim($configured) : '';

    if ($configured === '') {
        conc_abort(
            CONC_DATABASE_ENV.' is not set. Export the name of a disposable MySQL database first, '
            .'for example '.CONC_DATABASE_ENV.'=patient_crm_verify.'
        );
    }

    if (! str_ends_with($configured, CONC_DATABASE_SUFFIX)) {
        conc_abort(
            'a disposable database name has to end with "'.CONC_DATABASE_SUFFIX.'", got "'.$configured.'".'
        );
    }

    $application = conc_application_database();

    if ($application !== null && $configured === $application) {
        conc_abort('"'.$configured.'" is the database the application itself is configured against.');
    }

    return $configured;
}

/**
 * Boot the application against the disposable database.
 *
 * `$connect` is false only while the database may still be missing, which is the
 * case at the start of the setup step.
 */
function conc_boot(bool $connect = true): void
{
    static $booted = false;

    if ($booted) {
        return;
    }

    $booted = true;

    $database = conc_database_name();

    putenv('DB_CONNECTION=mysql');
    putenv('DB_DATABASE='.$database);
    $_ENV['DB_CONNECTION'] = 'mysql';
    $_ENV['DB_DATABASE'] = $database;
    $_SERVER['DB_CONNECTION'] = 'mysql';
    $_SERVER['DB_DATABASE'] = $database;

    require_once conc_root().'/vendor/autoload.php';

    $app = require conc_root().'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    config([
        'database.default' => 'mysql',
        'database.connections.mysql.database' => $database,
    ]);

    if (config('database.connections.mysql.driver') !== 'mysql') {
        conc_abort('the "mysql" connection is configured with a different driver.');
    }

    if ($connect) {
        conc_assert_connected_database();
        conc_forget_permission_cache();
    }
}

/**
 * Re-check, against the live connection, that this process is talking to the
 * disposable database. Called before every operation that writes or drops data.
 */
function conc_assert_connected_database(): void
{
    $expected = conc_database_name();
    $connection = DB::connection();
    $driver = $connection->getDriverName();

    if ($driver !== 'mysql') {
        conc_abort('the harness needs a MySQL connection to take real row locks, running on "'.$driver.'".');
    }

    $connected = $connection->getDatabaseName();

    if ($connected !== $expected) {
        conc_abort('connected to "'.$connected.'" while the disposable database is "'.$expected.'".');
    }
}

/**
 * Roles and permissions are cached in the application cache, so a worker that
 * started after another process changed them has to drop that cache first.
 */
function conc_forget_permission_cache(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * A server-level connection, opened without selecting a database: the disposable
 * database may not exist yet, and it is the one thing a drop must not need. The
 * credentials come from the application configuration and are never printed.
 */
function conc_server_pdo(): PDO
{
    $config = config('database.connections.mysql');

    return new PDO(
        sprintf('mysql:host=%s;port=%s', $config['host'], $config['port']),
        (string) $config['username'],
        (string) $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
}

/**
 * Create the disposable database when it is missing and rebuild it from the
 * migrations and seeders.
 *
 * Destructive: the name is verified again immediately before the schema is
 * dropped, and the database must carry the disposable suffix.
 */
function conc_reset_database(): void
{
    $database = conc_database_name();

    $server = conc_server_pdo();

    $server->exec(sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        $database,
    ));

    conc_assert_connected_database();

    /*
    | Only the roles, permissions and the administrator are seeded. The demo
    | data of `DatabaseSeeder` would fill the factories' unique values, and the
    | cases create every record they compete over themselves.
    */
    $exitCode = Artisan::call('migrate:fresh', [
        '--force' => true,
    ]);

    if ($exitCode === 0) {
        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RolePermissionSeeder',
            '--force' => true,
        ]);
    }

    if ($exitCode !== 0) {
        conc_abort('the schema could not be rebuilt on "'.$database.'": '.trim(Artisan::output()));
    }
}

function conc_config(): array
{
    global $argv;

    if (! isset($argv[1])) {
        fwrite(STDERR, "missing config argument\n");

        exit(98);
    }

    $config = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
    $config = is_array($config) ? $config : [];

    if (! empty($config['ready'])) {
        conc_signal((string) $config['ready']);
    }

    return $config;
}

function conc_acting_as(int $userId): User
{
    $user = User::query()->findOrFail($userId);

    Auth::setUser($user);

    return $user;
}

function conc_result(array $data): void
{
    echo 'RESULT:'.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
}

function conc_signal(string $path): void
{
    file_put_contents($path, (string) getmypid());
}

function conc_wait_for(string $path, float $timeoutSeconds = 25.0): bool
{
    $deadline = microtime(true) + $timeoutSeconds;

    while (microtime(true) < $deadline) {
        clearstatcache(true, $path);

        if (file_exists($path)) {
            return true;
        }

        usleep(10_000);
    }

    return false;
}

function conc_plain_request(string $method, string $uri, array $payload): Request
{
    $request = Request::create($uri, $method, $payload);

    $request->setUserResolver(static fn () => Auth::user());

    app('url')->setRequest($request);

    return $request;
}

/**
 * Build and resolve a FormRequest exactly as the router would before handing it
 * to a controller action.
 *
 * @param  class-string<FormRequest>  $class
 * @param  array<string, mixed>  $routeParams
 */
function conc_form_request(string $class, string $method, string $uri, array $payload, array $routeParams = []): FormRequest
{
    /** @var FormRequest $request */
    $request = $class::create($uri, $method, $payload);

    $request->setContainer(app());
    $request->setRedirector(app('redirect'));
    $request->setUserResolver(static fn () => Auth::user());

    $route = new Route($method, $uri, []);
    $route->bind($request);

    foreach ($routeParams as $name => $value) {
        $route->setParameter($name, $value);
    }

    $request->setRouteResolver(static fn () => $route);

    app('url')->setRequest($request);

    $request->validateResolved();

    return $request;
}

function conc_validation_errors(Throwable $exception): array
{
    if ($exception instanceof ValidationException) {
        return $exception->errors();
    }

    return ['_exception' => get_class($exception).': '.$exception->getMessage()];
}

function conc_finish(array $data): void
{
    conc_result($data);

    exit(0);
}
