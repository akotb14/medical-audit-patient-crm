# MySQL concurrency harness

The Pest suite runs on SQLite, in one process, without ever making two writes
compete. That is enough for the request-level behaviour, and it cannot show
whether the row locks the controllers take actually serialise anything. This
harness fills that gap: every case starts two real PHP processes, gives one of
them a row lock inside an open transaction, and lets the other run the real
controller action against the same row.

Nothing in this directory is part of the Pest suite. `phpunit.xml` collects
`tests/Unit` and `tests/Feature` only, so a plain `php artisan test` never runs
these files.

## What it needs

- A reachable MySQL server, using the host, port and credentials already
  configured in `.env`. The credentials are read from the application
  configuration and are never printed or written to a log.
- A database that may be destroyed. The harness creates it and drops it; it
  never touches the database the application itself uses.
- The seeder data, which the harness creates itself: the cases act as the seeded
  administrator from `RolePermissionSeeder`.
- SQLite is deliberately not supported here. These cases verify InnoDB row
  locks, and a run that does not take real locks would report success for the
  wrong reason. The harness aborts if the connection is not MySQL.

## Choosing the database

The name comes from the environment variable `CONCURRENCY_DB_DATABASE`, and
there is no default. Before any process connects, and again before every
destructive step, the name is checked:

- it must be set, and it must end in `_verify`;
- it must differ from `DB_DATABASE` in `.env`;
- the live connection must report the same name and the `mysql` driver.

A name that fails any check aborts the process with exit code 99 and writes
nothing. This is what keeps `DB_DATABASE=Clinizone` out of reach:

```bash
# bash
export CONCURRENCY_DB_DATABASE=patient_crm_verify
```

```powershell
# PowerShell
$env:CONCURRENCY_DB_DATABASE = 'patient_crm_verify'
```

## Setup, run, clean up

```bash
php tests/Concurrency/setup.php     # create the database, migrate:fresh, seed roles and the admin
php tests/Concurrency/run_all.php   # run every case, print the report
php tests/Concurrency/cleanup.php   # drop the database
```

Only `RolePermissionSeeder` is run, never `DatabaseSeeder`: the demo data fills
the factories' unique values, and every record a case competes over is created
by the case itself.

`setup.php` is only needed once; `run_all.php` rebuilds the same database at the
start of every run so that a run is repeatable (the MRN case pins a generated
value, which can only collide on a database that has not seen it before).
`cleanup.php` drops the database and is safe to run when it does not exist.

Exit codes: `0` when every check passed, `1` when a run finished but a check
failed, `96` when the seeded administrator is missing or unroled, `98` when a
worker was started without its configuration, `99` when a database check
aborted.

## Reading the output

Every case prints one `SCENARIO:` line holding JSON: the two processes, the row
as it ended up, and a `checks` map of named booleans. `run_all.php` ends with a
`SUMMARY:` line carrying the scenario and check counts, the names of the failed
checks, and the temporary directory the workers used for their signal files
(`%TEMP%/conc_run_<pid>` on Windows). Those directories are left in place on
purpose: the `RESULT:` lines of both processes are kept in the case payloads and
the signal files make it possible to re-run a single case by hand.

## The cases

| Case | What it holds the lock against |
| --- | --- |
| `visit_update_vs_complete` | an edit that was validated while the visit was open, arriving after the visit was completed |
| `visit_update_vs_cancel` | the same, against a cancellation |
| `visit_destroy_vs_complete` | a delete racing the completion that makes the visit history |
| `followup_complete_race` | a second completion that must not overwrite the outcome or the completion moment |
| `followup_complete_together` | two completions released at the same moment |
| `followup_status_vs_complete` | a status change arriving after the follow-up reached `done`, which is the end of the workflow |
| `followup_status_together` | two different status changes released at the same moment, one of which carries a completion moment |
| `complaint_assign_race` | an assignment racing the assignment that already moved the complaint out of `new` |
| `complaint_assign_together` | two assignments released at the same moment |
| `last_admin_delete_together` | two administrators deleting each other, one must survive |
| `last_admin_demote_together` | two administrators demoting each other, one must survive |
| `mrn_collision_race` | a registration racing the in-flight insert of the MRN it just generated |

Each case pairs a check on the row with a check on what the losing process was
told, so a refusal that silently wrote something fails the run. `worker_hold.php`
is the process that takes the lock; the `worker_*.php` files next to it are the
actors, and each one calls the controller the route calls.

## Limitations

- Timing-based, not deterministic. A case where the actor's action does not
  overlap the holder's transaction reports `actor_blocked_while_holder_held` as
  false and the run fails, rather than passing quietly. A slower machine needs
  a larger `hold_ms` in `run_all.php`.
- The actors call controller actions directly, with a request built by hand.
  Routing, middleware and session persistence are not part of what is verified
  here; the Pest suite covers those.
- One MySQL server, one database, no replication, no failover, and no isolation
  level other than InnoDB's default `REPEATABLE READ`.
- It leaves the disposable database behind when it fails, which is what
  `cleanup.php` is for.
- It does not run in CI: a CI job that runs it needs its own MySQL service and
  its own `CONCURRENCY_DB_DATABASE` value, and no test database secrets are
  committed here.
