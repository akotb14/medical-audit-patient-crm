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
| `followup_status_together` | two different status changes released at the same moment; both moves are legal, so the later one wins and the row has to belong to it |
| `complaint_assign_race` | an assignment racing the assignment that already moved the complaint out of `new` |
| `complaint_assign_together` | two assignments released at the same moment |
| `last_admin_delete_together` | two administrators deleting each other, one must survive |
| `last_admin_demote_together` | two administrators demoting each other, one must survive |
| `last_admin_delete_vs_demote_together` | a deletion racing a demotion, which reach the guard through two different service methods |
| `mrn_collision_race` | a registration racing the in-flight insert of the MRN it just generated |

The three last-administrator cases run `RUN_LAST_ADMIN_ITERATIONS` (5) times
each: they guard an intermittent failure that depends on which process reaches
the lock first, so one pass proves nothing. Every iteration rebuilds its premise
— exactly two administrators, both disposable accounts — and throws if it
cannot, so a broken fixture fails the run instead of reporting a false pass.

Each case pairs a check on the row with a check on what the losing process was
told, so a refusal that silently wrote something fails the run. `worker_hold.php`
is the process that takes the lock; the `worker_*.php` files next to it are the
actors, and each one calls the controller the route calls.

## Last-administrator protection

Concurrent administrative operations must never leave the application with zero
active administrators. `UserService::update()` and `UserService::delete()` reach
that decision through a single serialisation point: the `Admin` row in `roles`
is locked first, then the current administrator memberships are read with a
direct locking join over `users`, `model_has_roles` and `roles`.

Both parts are load-bearing. Locking only the target user row serialises nothing
when the two transactions have different targets, and reading membership through
a `whereHas` subquery answers it from the transaction's read view, so a process
that waited for the lock still counts the memberships from before the commit it
waited for. See the `UserService` docblocks for the full reasoning.

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

## Known state (2026-11-10)

A full run now covers 13 scenarios and 69 checks. Five consecutive runs after
the last-administrator fix were green, which is 25 iterations of each
last-administrator race and 75 concurrent races in total.

- The phone and follow-up fixtures were corrected: `mrn_collision_race` submits
  an international number, and `followup_status_together` starts from `missed`
  and submits the two statuses the endpoint accepts. Both actors are accepted
  and the later write wins, so the case asserts that the final row — status,
  `updated_by` and the empty completion moment — belongs entirely to the last
  writer; it no longer claims only one of the two can be answered.
- `visit_destroy_vs_complete` no longer asserts that the delete was blocked. When
  the completion commits first the policy refuses the delete on the spot, which
  is correct, and the outcome checks carry the invariant.
- Timing-based checks remain timing-based. `actor_blocked_while_holder_held` and
  `run_was_blocked_by_holder()` need the actor's action to overlap the holder's
  transaction; a heavily loaded machine can still report a false failure there,
  which `hold_ms` in `run_all.php` is the knob for.
- The harness still does not run in CI and does not test routing, middleware or
  session persistence.
