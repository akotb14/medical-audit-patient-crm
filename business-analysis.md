# Clinical Care Center (Medical Audit & Patient CRM) — Analysis & Requirements

> **Purpose.** This document is the business analysis and requirements reference for the
> application in this repository. It is written to be read on its own: every rule, state,
> screen and constraint it states is enforced by the code it names, and nothing is described
> here that the application does not do.

---

> **Method — 2026-11-10.** This document was produced by reading the application, not by
> restating an earlier plan. The source of truth was, in order of authority: the schema in
> `database/migrations`, the workflow code in `app/Services`, the validation in
> `app/Http/Requests`, the authorization in `app/Policies` and `database/seeders/RolePermissionSeeder.php`,
> the route table in `routes/`, the screens in `resources/views`, and the Pest suite in
> `tests/Feature`. Where two of those disagreed, the executable one won and the disagreement is
> recorded in §14 and §15 rather than smoothed over.
>
> The documentation conventions are the same throughout: a requirement is numbered (`FR-n`), a
> business rule is numbered (`BR-n`), and every state a record can hold is shown as a transition
> table rather than as prose. Nothing in this file is a proposal; §13 is the only section that
> describes work that does not exist.

---

> **Scope of what is implemented — 2026-11-10.** The repository is named after a wider product
> idea: a medical audit platform with a patient CRM attached. This application delivers the CRM
> and the clinic-operations half. The audit/compliance half — audit templates, sessions, scoring,
> corrective actions — and the claims module are **not implemented**; §13 records them as
> extensions. §15 lists what is deliberately not claimed anywhere in this document.

---

## 1. Overview

The application is an Arabic-first, right-to-left clinic operations CRM. It follows a patient
from registration through visits, follow-ups, satisfaction surveys and complaints, and gives
management a KPI dashboard over the same data. It is built for two kinds of staff — an
administrator who runs the clinic, and a receptionist who works with patients all day — and it
records what happens at the desk or on the phone on their behalf.

It is a server-rendered Laravel application with a hand-built Blade interface. There is no
JavaScript framework, no API layer, no queue worker and no external service dependency.

### 1.1 Goals

- **Never lose a patient.** Every visit can carry a follow-up, and a follow-up that was not
  answered becomes visible without anyone remembering to look for it.
- **Measure satisfaction and react to it.** One survey per completed visit, and a rating below
  three opens a complaint in the same transaction, so an unhappy patient is never only a
  statistic.
- **Give management one screen.** A single dashboard over visits, follow-ups, satisfaction,
  complaints, clinics, doctors and patients.
- **Keep the history intact.** Records are archived, never removed; every write names the staff
  member who made it; workflow changes take a row lock so two people cannot both complete the
  same step.
- **Be usable by an Arabic-speaking clinic.** Arabic interface, Arabic validation and framework
  messages, RTL layout, Cairo time zone.

### 1.2 Non-goals

- **No patient-facing login or portal.** The patient never opens the system; staff record what
  the patient says.
- **No messaging.** No SMS, WhatsApp or email is sent. There is no notification channel of any
  kind.
- **No clinical record, no billing, no insurance, no appointment calendar.** Visits carry a
  reason and a doctor, not a diagnosis, a prescription or a charge.
- **No multi-tenancy.** One organisation. `clinics` are departments inside it, not separate
  customers, and there is no tenant column anywhere.
- **No English interface.** The UI ships in Arabic only; there is no language switcher.

---

## 2. Actors

| Actor | Uses the system? | What they do |
|---|---|---|
| **Administrator** (`Admin`) | Yes, all day | Everything a receptionist can do, plus the dashboard, staff accounts and roles, clinics, doctors, visit edits and deletion, follow-up edits and deletion, survey deletion, and every complaint action: assignment, stage advancement, reopening and deletion |
| **Receptionist** (`Receptionist`) | Yes, all day | Registers patients, schedules visits, completes or cancels visits, completes and re-schedules follow-ups, records surveys, files complaints, edits and deletes patient files |
| **Patient** | No | A source of information only. Feedback is given verbally or by phone and entered by staff |

Authorization is not a role check scattered through the code. It is a catalogue of **38
permissions** (`app/Enums/PermissionEnum.php`) checked by eight policies, one per domain model:

| Group | Permissions | Admin | Receptionist |
|---|---|---|---|
| Users | `users.view`, `users.create`, `users.update`, `users.delete` | ✔ | — |
| Clinics | `clinics.view`, `clinics.create`, `clinics.update`, `clinics.delete` | ✔ | view only |
| Doctors | `doctors.view`, `doctors.create`, `doctors.update`, `doctors.delete` | ✔ | view only |
| Patients | `patients.view`, `patients.create`, `patients.update`, `patients.delete` | ✔ | all except delete |
| Visits | `visits.view`, `visits.create`, `visits.update`, `visits.status`, `visits.delete` | ✔ | view, create, status |
| Follow-ups | `followups.view`, `followups.create`, `followups.complete`, `followups.status`, `followups.update`, `followups.delete` | ✔ | view, create, complete, status |
| Clinics/Surveys | `surveys.view`, `surveys.create`, `surveys.update`, `surveys.delete` | ✔ | view, create |
| Complaints | `complaints.view`, `complaints.create`, `complaints.update`, `complaints.assign`, `complaints.advance`, `complaints.reopen`, `complaints.delete` | ✔ | view, create |

The administrator holds all 38. The receptionist holds 16: the 22 excluded permissions are the
four user permissions, every write permission on clinics and doctors, `patients.delete`,
`visits.update`, `visits.delete`, `followups.update`, `followups.delete`, `surveys.update`,
`surveys.delete`, and the five complaint permissions other than view and create.

Two capabilities are narrower than their permission and are worth stating separately:

- **The current assignee may advance their own complaint.** `ComplaintPolicy::advance()` grants
  the action to anyone holding `complaints.advance`, *or* to the assignee of a complaint whose
  status is neither `new`, `resolved` nor `closed`. A receptionist assigned a complaint can move
  it from `assigned` to `investigating` to `resolved` without the administrator permission.
- **The dashboard is administrator-only at the route.** `GET /dashboard` carries both
  `auth` and `role:Admin` middleware, and the sidebar link is wrapped in `@role('Admin')`. A
  receptionist is redirected to the clinics list after signing in.

---

## 3. Scope

The delivered modules, each of which owns its own routes, controller, service, form requests,
policy, screens and tests:

1. **Authentication.** Username-and-password session login, logout with session invalidation,
   and two rate-limit buckets.
2. **Roles and permissions.** Two roles, 38 permissions, eight policies, seeded catalogue.
3. **Patients.** Full clinical registration with an auto-generated MRN, search, filtering,
   archive and restore, and a per-patient visit history.
4. **Clinics.** Departments with a lifecycle status, grouping doctors.
5. **Doctors.** Consultants linked to a clinic, with specialty, contact details, a schedule and
   a status.
6. **Visits.** Scheduling, a three-state lifecycle, completion that can create the follow-up in
   the same transaction, and filtering.
7. **Follow-ups.** A four-state lifecycle, a completion action with a mandatory outcome, and an
   overdue scan that runs every minute.
8. **Satisfaction surveys.** One per completed visit, with the low-rating reply.
9. **Complaints.** Automatic and manual intake, a five-stage workflow, assignment, reopening,
   filters and statistics.
10. **Dashboard.** KPI cards and workflow tables for administrators.
11. **Cross-cutting.** Arabic localization, branded error pages, and a MySQL concurrency harness.

---

## 4. Core Workflow

### 4.1 End-to-end journey

```
Register the patient file  (MRN issued; phone reserved)
      |
      v
Schedule a visit  (status: created; date today or later)
      |
      +--> cancel --------------------------------------> cancelled  (terminal)
      |
      +--> complete only ----------------------------------------+
      |                                                          |
      +--> complete and schedule a follow-up                      |
      |            |                                             |
      |            v                                             |
      |     Follow-up: scheduled                                 |
      |            |                                             |
      |            +--> done        (outcome note required)      |
      |            +--> cancelled                                 |
      |            +--> missed      (overdue scan only)          |
      |                     |                                    |
      |                     +--> scheduled  (re-schedule)        |
      |                     +--> cancelled                       |
      |                                                          |
      v                                                          v
Visit: completed  — frozen: date, doctor, reason and patient cannot change
      |
      |  the completion response opens the survey dialog on the register
      v
Record the satisfaction survey  (one per visit; rating 1-5; optional comment)
      |
      +-- rating >= 3 --> counted in the satisfaction KPIs only
      |
      +-- rating <  3 --> one complaint is opened in the same transaction
                              source = survey, status = new
                              |
                              v
              new --> assigned --> investigating --> resolved --> closed
                         ^                             |
                         |                             |
                         +------- reopen (Admin) ------+
                              from resolved or closed
```

A complaint can also enter at `new` without a survey: staff file one manually from the patient's
file at the desk. That is the `source = manual` branch and it joins the same lifecycle.

### 4.2 Step-by-step description

1. **Register the patient.** Staff enter the full name, phone, gender, birth date and blood
   group, and optionally the address, allergies, chronic conditions and an emergency contact.
   The phone must be a valid international number and may not belong to any patient file,
   including an archived one. The service issues a unique `MRN-` identifier and stores the file
   as `active`.
2. **Schedule the visit.** The visit names an existing, non-archived patient, an existing
   doctor, a reason of up to 500 characters, and a date that is today or later. It is stored as
   `created`; the doctor's clinic is reached through the doctor.
3. **Complete or cancel the visit.** From the register, staff either complete the visit on its
   own, complete it and schedule its follow-up in one action, or cancel it. The follow-up branch
   is refused if the visit already has one, and writes a `scheduled` follow-up with a due date
   in the future and a reason. Completion freezes the visit.
4. **Record the survey.** Completion returns the register with a flag that opens the survey
   dialog on the completed visit. Only a completed visit accepts a survey, and only one survey
   per visit is possible. The rating is an integer from 1 to 5; the comment is optional.
5. **React to a low rating.** A rating of 1 or 2 creates the survey **and** a complaint in one
   database transaction. The complaint is `source = survey`, `status = new`, is linked through
   `complaints.survey_id` (unique, so a survey can never raise two), and is titled
   «ملاحظات سلبية في استبيان رضا المرضى». A failed complaint insert rolls the survey back, so a
   dissatisfied patient cannot end up with a recorded low rating and no ticket.
6. **Work the follow-up.** A scheduled follow-up is completed with a mandatory outcome note, or
   cancelled, or re-scheduled to a future date. A follow-up whose due date has passed while it
   is still `scheduled` is flipped to `missed` by the scheduled command, and from `missed` staff
   can re-schedule it or cancel it. `done` is the end of the workflow.
7. **Work the complaint.** An unassigned complaint sits at `new`. Assignment moves it to
   `assigned` and is only possible from `new`. The assignee starts the investigation, and
   resolution requires a written resolution note of up to 2,000 characters. Closing is the
   administrator's step and records `closed_at`. Reopening from `resolved` or `closed` returns
   the complaint to `investigating` and clears both stage timestamps.
8. **File a manual complaint when there is no survey.** Staff choose the patient, write a title
   of up to 255 characters and a description, and may assign it immediately. Choosing an
   assignee at creation takes the same `complaints.assign` ability as the assign action, so the
   field cannot be used to reach a stage the actor may not assign into.
9. **Read the dashboard.** The administrator sees visit counts and completion rate, follow-up
   counts with the next five due, satisfaction counts and averages, complaint counts per stage
   and the four most recent, clinic load by doctor count, doctor and patient totals, and the
   seven most recent visits.

---

## 5. State Machines

Status values are stored as plain strings in enumerated columns. The transition tables below are
the code: `FollowUpService::TRANSITIONS`, `FollowUpService::canTransitionTo()`,
`Complaint::nextStatus()`, `Complaint::canReopen()` and `VisitService::changeStatus()`.

### 5.1 Follow-up

| From | To | Trigger | Rule |
|---|---|---|---|
| `scheduled` | `done` | Staff, completion action | Outcome note required (1–500 characters); `completed_at` recorded |
| `scheduled` | `cancelled` | Staff, status action | — |
| `scheduled` | `missed` | `followups:update-overdue`, every minute | Only rows whose `due_date` is already in the past |
| `missed` | `scheduled` | Staff, edit or status action | New due date must be in the future |
| `missed` | `cancelled` | Staff, status action | — |
| `cancelled` | `scheduled` | Staff, status action | Should a cancelled follow-up need a future date, the edit form supplies one |
| `done` | — | — | Terminal. Both the edit path and the status path refuse it |

Two guards surround the table:

- **`missed` is never reachable by hand.** The status endpoint accepts only `scheduled` and
  `cancelled`, and `FollowUpService::update()` uses the same transition table, which has no edge
  into `missed`.
- **The completion moment follows the status.** Any write that leaves the row in a status other
  than `done` clears `completed_at`, and any write that reaches `done` sets it if it is not
  already set.

### 5.2 Complaint

| From | To | Who | Rule |
|---|---|---|---|
| — | `new` | Staff, or a rating below 3 | Manual intake, or the automatic branch of the survey transaction |
| `new` | `assigned` | `complaints.assign`, or the advance action | An assignee must be chosen; the id must exist in `users` |
| `assigned` | `investigating` | `complaints.advance`, or the assignee | — |
| `investigating` | `resolved` | `complaints.advance`, or the assignee | `resolution_note` required, 1–2,000 characters; `resolved_at` recorded |
| `resolved` | `closed` | `complaints.advance` | `closed_at` recorded; `resolved_at` preserved |
| `resolved` | `investigating` | `complaints.reopen` (Admin) | Reopen; both timestamps cleared |
| `closed` | `investigating` | `complaints.reopen` (Admin) | Reopen; both timestamps cleared |

`closed` has no forward edge: a closed complaint that is advanced again is refused with
«لا يمكن الانتقال من الحالة الحالية.».

A complaint cannot move to `resolved` or `closed` without a resolution note. The note is
validated **inside** the transaction that holds the row lock, so the check and the write cannot
be separated.

### 5.3 Visit and survey

**Visit.** The visit has a real lifecycle, not an informal status:

| From | To | Trigger | Rule |
|---|---|---|---|
| — | `created` | Staff, scheduling a visit | Date must be today or later |
| `created` | `completed` | Staff, completion action | Optionally writes the visit's single follow-up in the same transaction |
| `created` | `cancelled` | Staff, cancel action | — |
| `completed` | — | — | Frozen: date, doctor, reason and patient cannot be edited, and the visit cannot be deleted |
| `cancelled` | — | — | Also final; the edit path refuses it |

Editing is refused for both closed statuses, and deletion is refused for `completed` at the
policy layer *and* again inside the locked transaction.

**Survey.** A survey is created once per completed visit and has no edit route at all: the
routes are `index`, `store`, `show` and `destroy` only. That is deliberate — the satisfaction
figures are only trustworthy if a rating cannot be adjusted after the fact. Deletion is the
only correction, and it is refused when the survey raised a complaint, because deleting it would
delete the evidence behind an open ticket.

---

## 6. Business Rules

Each rule is stated as the application enforces it, with the enforcement point named.
§15 maps the same rules to the tests that cover them.

| # | Rule | Enforced by |
|---|---|---|
| **BR-1** | A patient phone number is unique across every patient file, archived or not; an MRN is unique across every file including archived ones | `patients.phone` and `patients.mrn` unique indexes; `BasePatientRequest`; the MRN generator in `PatientService::register()` |
| **BR-2** | A visit belongs to an existing, non-archived patient and to an existing doctor | `CreateVisitRequest` (`Rule::exists(...)->whereNull('deleted_at')`), foreign keys |
| **BR-3** | A new visit is always stored as `created`, and its date may not be in the past | `VisitService::create()`, `CreateVisitRequest` |
| **BR-4** | A follow-up due date must be in the future, unless the submitted value is the stored value unchanged — which is what lets an overdue follow-up be saved without moving its date | `UpdateFollowUpRequest::rescheduleRule()` |
| **BR-5** | A visit has at most one follow-up | `follow_ups.visit_id` unique index; `VisitService::changeStatus()` refuses a second one |
| **BR-6** | A visit has at most one survey, and only a completed visit accepts one | `surveys.visit_id` unique index; `SurveyService::submit()` returns `recorded = false` for any other status |
| **BR-7** | A survey rating is an integer from 1 to 5 | `CreateSurveyRequest` |
| **BR-8** | A rating below 3 opens exactly one complaint, in the same transaction as the survey, with `source = survey` and `status = new` | `SurveyService::submit()`; `complaints.survey_id` unique index |
| **BR-9** | A complaint cannot be resolved or closed without a resolution note | `ComplaintService::advance()` inside the row lock |
| **BR-10** | A complaint can only be assigned while it is `new`, and only to an existing user | `ComplaintService::assign()`, `AssignComplaintRequest`, `ComplaintService::create()` |
| **BR-11** | `missed` is reachable only through the scheduled command | `UpdateFollowUpStatusRequest`, `FollowUpService::TRANSITIONS`, `UpdateOverdueFollowUps` |
| **BR-12** | `done` is terminal for a follow-up | `FollowUpService::complete()`, `changeStatus()`, `update()` |
| **BR-13** | A completed visit is frozen and cannot be deleted; a cancelled visit cannot be edited | `VisitService::update()`, `delete()`, `VisitPolicy::delete()` |
| **BR-14** | A follow-up cannot be re-pointed at another patient or visit | `FollowUpService::update()` unsets `patient_id` and `visit_id` |
| **BR-15** | A complaint's patient is immutable after filing, including when that patient is archived afterwards | `UpdateComplaintRequest::immutablePatientRule()` |
| **BR-16** | A survey that raised a complaint cannot be deleted | `SurveyService::delete()` |
| **BR-17** | A clinic cannot be deleted while any doctor is attached, active or not | `ClinicService::delete()` under a row lock |
| **BR-18** | Deleting a doctor is a soft delete that also marks the doctor `inactive`; the visits that reference it keep resolving to it | `DoctorService::delete()`, `Doctor` uses `SoftDeletes`, `Visit::doctor()` uses `withTrashed()` |
| **BR-19** | Patients are archived, never removed. The archiving user is recorded, the file can be restored, and its phone stays reserved throughout | `PatientService::archive()` / `restore()`, `SoftDeletes`, `deleted_by`, `deleted_at` index |
| **BR-20** | The last administrator can be neither demoted nor deleted. Sequential attempts are refused; two administrators demoting each other at the same moment can both pass and leave zero administrators (OQ-12) | `UserService::lockAdministrators()` (row locks), `update()`, `delete()` |
| **BR-21** | Staff accounts are soft-deleted, so every audit reference — `created_by`, `updated_by`, `assigned_to`, `deleted_by` — keeps resolving after the account leaves the list | `User` uses `SoftDeletes`; every relation declares `withTrashed()` |
| **BR-22** | A user may not delete the account they are signed in with, and only an administrator may create another administrator | `UserController::destroy()`, `UserService::create()` |
| **BR-23** | Every domain write records the acting user in `created_by` and, when it changes a record, in `updated_by` | Services and controllers take `Auth::id()` |
| **BR-24** | Sign-in is rate-limited to 5 attempts per username-and-IP and 20 per IP per minute, and a failure message never reveals whether the username exists | `LoginRequest`, `AuthController::login()` |
| **BR-25** | The dashboard is administrator-only | `role:Admin` middleware on `GET /dashboard` |
| **BR-26** | Every workflow write re-reads its row under a lock inside a transaction, so two competing state changes on the same record cannot both pass. The last-administrator count is the exception: it is read through a membership subquery, and the mutual-demotion race survives it (OQ-12) | `VisitService`, `FollowUpService`, `ComplaintService`, `UserService`, `ClinicService`, `PatientService::archive()` |

---

## 7. Functional Requirements

FR-1 to FR-8 are the eight requirement steps this analysis has always taken, kept in the same
order and serving the same purpose. Two modules the current application delivers were not among
them, so they are appended as FR-9 and FR-10 rather than inserted, which would have renumbered
every step after them.

### FR-1 Authentication and roles
- Sign in with a username and password; the session id is regenerated on success.
- Sign out invalidates the session and regenerates the CSRF token.
- Two rate-limit buckets: 5 attempts per `username|ip` and 20 per IP, each decaying over 60
  seconds; both are cleared on success.
- Two roles and 38 permissions, seeded idempotently; permissions are cached and the cache is
  flushed whenever the catalogue changes.
- Administrators land on the dashboard; receptionists land on the clinics list; both the root
  path and the login response follow the same rule.
- Accounts are soft-deleted and listed with their role; the last administrator is protected.

### FR-2 Patients
- Register, edit, view, search and filter by blood group, paginated at 20 per page.
- Search spans name, MRN and phone.
- The MRN is generated by the service, unique across archived files too, retried up to five
  times before failing with an Arabic message.
- The profile shows the demographics, the clinical fields, and the patient's visit history with
  per-status counts and a pager.
- Archive and restore, with the archiving user recorded. The archived-files screen is restricted
  to the permission that also governs archiving.
- An archived file does not open on the profile or edit screen, does not appear in patient
  dropdowns, and cannot be chosen for a new visit or complaint.

### FR-3 Visits
- Schedule a visit for a patient and a doctor with a reason and a date.
- Filter by status, date, doctor, patient and free text; search spans the reason, the patient's
  name and MRN, the doctor's name and the acting staff member's name; paginated at 20.
- Complete on its own, complete with a follow-up (one transaction, one follow-up), or cancel.
- Edit while `created` only; the patient cannot be changed to another patient, and a status
  submitted to the update endpoint is ignored.
- Delete while not `completed`.
- The register carries the per-row dialogs for completing a visit and for the survey prompt that
  opens after a completion.

### FR-4 Follow-ups
- Created only by completing a visit; there is no standalone create route.
- Filter by status and free text; the register shows counts for total, scheduled, done, missed,
  cancelled and overdue, and lists the next scheduled items first.
- Complete with an outcome note, which is mandatory; change status between `scheduled` and
  `cancelled`; edit the due date, reason, status and note; delete.
- The overdue scan runs through `php artisan followups:update-overdue`, and from the scheduler
  every minute with `withoutOverlapping()`.

### FR-5 Satisfaction surveys
- Recorded from a modal on the visits register or the dashboard, against a completed visit.
- List with the patient, visit, doctor, recording staff member and any complaint raised;
  filter by search text (patient name or MRN) and an exact rating; paginated at 20.
- Statistics: total, average rating, positive and negative counts, and the number of complaints
  raised from surveys.
- No edit; delete only, and only when no complaint is attached.
- Every rating is rendered through a label that names the score, e.g. «ممتاز جداً (5/5)» or
  «سيء جداً (1/5) - شكوى حرجة».

### FR-6 Complaints
- Manual intake with a non-archived patient, a title, a description and an optional assignee;
  automatic intake from a rating below 3.
- Filter by status, source, assignee (a specific member of staff, `unassigned`, or all) and free
  text; search spans the title, the patient's name, phone and MRN, and the assignee's name;
  paginated at 15.
- Statistics for every stage, the automated count and the unassigned count.
- Assign, advance, reopen and delete through dedicated endpoints; edit the title and description
  only.
- The detail page shows a five-step lifecycle indicator, the stage timestamps once they exist,
  the linked survey when the complaint came from one, and the resolution note.

### FR-7 Dashboard
Administrator-only, computed in `DashboardService::summary()` so a figure and the rate derived
from it can never disagree:

- **Visits:** total, completed, scheduled, cancelled, completion rate.
- **Follow-ups:** total, scheduled, done, missed, cancelled, and the next five scheduled with
  patient and doctor.
- **Satisfaction:** total surveys, average rating, the count at each rating, the low-rating
  count (1 and 2), and the positive rate (4 and 5 over the total).
- **Complaints:** total, per stage, the active count (`new` + `assigned` + `investigating`), the
  number raised from surveys, and the four most recent.
- **Clinics:** total, active, and the six clinics with the most doctors.
- **Doctors:** total, active.
- **Patients:** active total, and the number carrying a recorded allergy.
- **Recent visits:** the seven most recent by date, then id.

All figures are computed over the whole data set; there is no reporting-period selector (OQ-3).

### FR-8 Attribution and traceability
This is the step that formerly described an activity log. A separate log is not implemented, so
the step is answered by what the application does instead: every write is attributed to a staff
account on the record itself, and the reference survives the account leaving the list.

- Every domain table records `created_by` and, where a record can change, `updated_by`;
  patients also record `deleted_by`.
- There is no separate activity log, no event, no listener and no job. The audit trail is the
  record itself plus these columns.
- Relations to staff use `withTrashed()`, so an archived account still names itself on the
  records it touched.

### FR-9 Clinics
Not among the original requirement steps; the current application delivers it.
- List, create, view, edit and delete; a status of `active`, `maintenance` or `inactive` that is
  shown as «جاهزة للتشغيل (نشطة)», «تحت الصيانة» or «مغلقة مؤقتاً».
- The index shows every clinic with its doctors and a cached doctor count; a clinic with no
  doctors renders without a link.
- Deletion is refused while any doctor is attached, including inactive doctors.

### FR-10 Doctors
Not among the original requirement steps; the current application delivers it.
- List, create, view, edit and delete; fields are full name, email, phone, specialty, clinic,
  schedule days, notes and status (`active`, `holiday`, `inactive`).
- Email and phone are unique among doctors, enforced by validation.
- The index filters by search text, clinic and status; the detail page shows the doctor's visit
  history with the same eager-loaded relations as the patient history.
- The doctor dropdown offers active doctors only, keeps a doctor who has since left the active
  list when one is already selected, and is bounded at 500 entries.

---

## 8. Data Model

Eight domain tables, plus the framework's `sessions`, `cache`, `jobs` and `telescope_entries`
tables and the five spatie permission tables.

```
users
  id, name, username (unique), password, remember_token,
  timestamps, deleted_at                    -- soft deleted
  (roles and permissions via spatie: roles, permissions,
   model_has_roles, model_has_permissions, role_has_permissions)

patients
  id, mrn (unique), fullname, phone (unique, 15),
  gender (male|female), birthdate (date),
  blood_group (A+|A-|B+|B-|O+|O-|AB+|AB-),
  address, allergies, chronic_diseases,
  emergency_name, emergency_phone, status (active|inactive),
  created_by (FK users, restrict), updated_by (FK users, restrict, null),
  deleted_at (indexed), deleted_by (FK users, nullOnDelete),
  timestamps                                -- soft deleted

clinics
  id, name, description, status (active|maintenance|inactive),
  created_by (FK users), updated_by (FK users, null),
  timestamps                                -- index on status

doctors
  id, fullname, phone (15, null), email (unique, null),
  specialty, schedule_days (500), notes (500),
  status (active|holiday|inactive),
  clinic_id (FK clinics, nullOnDelete, null),
  created_by (FK users), updated_by (FK users, null),
  timestamps, deleted_at                    -- soft deleted; index on status

visits
  id, patient_id (FK patients, nullOnDelete, null),
  doctor_id (FK doctors, nullOnDelete, null),
  date (datetime), reason (500),
  visit_status (created|completed|cancelled),
  created_by (FK users), updated_by (FK users, null),
  timestamps                                -- indexes: (visit_status, date), (date)

follow_ups
  id, visit_id (FK visits, cascade, UNIQUE),
  patient_id (FK patients, cascade),
  due_date (datetime), status (scheduled|done|missed|cancelled),
  reason (500), outcome_note (500, null),
  completed_at (datetime with time zone, null),
  created_by (FK users), updated_by (FK users, null),
  timestamps                                -- index: (status, due_date)

surveys
  id, visit_id (FK visits, cascade, UNIQUE),
  patient_id (FK patients, cascade),
  rating (integer, default 3), comment (text, null),
  created_by (FK users, restrict), updated_by (FK users, null),
  timestamps                                -- index: (rating, created_at)

complaints
  id, patient_id (FK patients, cascade),
  survey_id (FK surveys, cascade, UNIQUE, null),
  source (survey|manual), title (255), description (text),
  status (new|assigned|investigating|resolved|closed),
  assigned_to (FK users, restrict, null),
  resolution_note (text, null),
  resolved_at (datetime, null), closed_at (datetime, null),
  created_by (FK users, restrict), updated_by (FK users, null),
  timestamps                                -- index: (status, created_at)
```

**Relationships.**

- A patient has many visits, and each visit belongs to one patient. The relation includes
  archived patients, so the history survives an archive.
- A clinic has many doctors; a doctor belongs to at most one clinic. Deleting a clinic
  force-nulls the link, but the service refuses that deletion while doctors exist.
- A doctor has many visits; a visit names one doctor.
- A visit has **zero or one** follow-up and **zero or one** survey, enforced by unique indexes on
  each `visit_id`, not only by application code.
- A survey has zero or one complaint, through `complaints.survey_id`, which is unique and
  nullable. The direction matters when integrating: there is no `complaint_id` on `surveys`.
- A complaint belongs to one patient and optionally to one survey, and is optionally assigned to
  one user.
- A patient has many follow-ups, surveys and complaints directly, in addition to reaching them
  through visits.

**Ownership columns.** `created_by`, `updated_by`, `deleted_by` and `assigned_to` are foreign
keys to `users`. `users` and `doctors` are soft-deleted, so those references never dangle;
`users` are never removed, which is what keeps BR-21 true.

**Indexes chosen for the read paths.** `follow_ups(status, due_date)` serves the overdue scan
and the status filter; `visits(visit_status, date)` and `visits(date)` serve the register and the
date filter; `complaints(status, created_at)` serves the stage tiles; `surveys(rating,
created_at)` serves the rating filter and the satisfaction aggregates; `patients(status)`,
`doctors(status)`, `clinics(status)` serve the "active only" dropdowns and counts; and
`patients(deleted_at)` serves the archived list, which soft deletes alone do not index.

---

## 9. Screens

The application uses hand-built Blade screens; there is no admin-panel package.

1. **Login** — username, password, one error area.
2. **Dashboard** (Admin) — KPI cards, the next follow-ups, recent complaints, recent visits,
   clinic load.
3. **Patients** — register with search and blood-group filter; create; edit; profile with the
   visit history and its pager; archived files with a restore action.
4. **Clinics** — register with doctor counts; create; edit; detail with the attached doctors.
5. **Doctors** — register with search, clinic and status filters; create; edit; detail with the
   visit history.
6. **Visits** — register with status, date, doctor, patient and text filters; create; edit;
   detail with the lifecycle actions and the completion dialogs.
7. **Follow-ups** — register with status filter and statistics tiles; edit; detail with the
   completion action; the completion and delete dialogs.
8. **Surveys** — register with search and rating filters; detail with the linked complaint.
   There is no create screen: surveys are recorded from the modal on the visits register and the
   dashboard.
9. **Complaints** — register with the four filters, statistics tiles and a quick-assign dialog;
   manual intake; edit; detail with the lifecycle indicator, the stage actions and the
   resolution note.
10. **Users** (Admin) — register with role and count tiles; create; edit. There is no detail
    screen.
11. **Error pages** — 403, 404, 419, 429 and 500, all rendered from one shared component.

Navigation is a persistent right-hand sidebar in three labelled sections — operational modules,
medical structure, and security and staff — with the staff section and the dashboard link gated
by permission or role. Below the `lg` breakpoint the sidebar becomes a drawer with an overlay
and its own close control.

---

## 10. Technical Design

### 10.1 Architecture

```
routes/web.php + one route file per module
        |
        v
Middleware        auth, role:Admin, spatie role/permission aliases
        |
        v
Form Request      shape and format of the input        app/Http/Requests
        |
        v
Controller        HTTP only: authorize, call a service, choose a view
        |
        v
Policy / Gate     who may perform the action           app/Policies
        |
        v
Service           business rules, transactions, row locks, workflow state
        |
        v
Query object      filter and aggregate SQL             app/Queries
        |
        v
Eloquent model    relations, casts, scopes, labels     app/Models
        |
        v
MySQL / InnoDB
```

- **Services hold the workflow.** Nine services (patients, clinics, doctors, visits, follow-ups,
  surveys, complaints, users, dashboard) own the transactions, the `lockForUpdate()` calls and
  the transition checks. Controllers never write a model directly.
- **Query objects hold the read paths.** Six classes (patient, doctor, visit, follow-up, survey,
  complaint) build the index filters and the statistics aggregates, so the same filter is never
  written twice.
- **Form requests hold the shape of the input**, with Arabic messages on every rule.
- **Policies hold the authorization**, one per model, each delegating to a single permission
  constant, with `restore` and `forceDelete` explicitly denied where no such operation exists.
- **Enums.** `RoleEnum` (two cases) and `PermissionEnum` (38 cases, with a `values()` helper) are
  load-bearing. `GenderEnum` and `BloodGroupEnum` are used by the factories. `AccountStatusEnum`
  is declared but not referenced anywhere (§14).
- **Scheduled command.** `UpdateOverdueFollowUps` — `followups:update-overdue` — is a single
  query, registered in `routes/console.php` to run every minute with `withoutOverlapping()`.
- **Caching.** Clinic dropdown data (1 hour), the active doctor count (1 hour) and the
  per-clinic doctor counts (1 hour), each flushed by the write that invalidates it.
- **No events, listeners or jobs.** The directory does not exist, and no workflow depends on
  asynchronous work.

### 10.2 Packages

| Package | Version | Role |
|---|---|---|
| `laravel/framework` | ^13.17 (13.34.0 installed) | The application |
| `php` | ^8.3 required; 8.5.12 verified | Runtime |
| `spatie/laravel-permission` | ^8.3 | Roles, permissions, middleware aliases |
| `propaganistas/laravel-phone` | ^6.1 | International phone-number validation on patient files |
| `laravel/tinker` | ^3.0 | Console |
| `pestphp/pest`, `pestphp/pest-plugin-laravel` | ^5.3 / ^5.0 | Test framework, in-memory SQLite |
| `laravel/pint` | ^1.27 | Code style |
| `laravel/telescope`, `laravel/pail`, `laravel/boost` | dev | Local debugging and tooling |
| `tailwindcss`, `@tailwindcss/vite`, `vite`, `laravel-vite-plugin` | 4.x, 8.x, ^3.1 | Styling and assets |

Deliberately absent: an admin-panel package, an activity-log package, a static-analysis package,
Sanctum, and any queue or broadcasting dependency in application code.

### 10.3 Quality bar

- **Factories and seeders for every model.** Ten seeders run in foreign-key order — roles and
  permissions, then staff, then clinics, doctors, patients, visits, follow-ups, surveys and
  complaints — producing synthetic Arabic clinical data. Every factory offers reusable states
  rather than forcing each test to spell out a row.
- **A Pest feature suite** of 171 tests and 612 assertions, run against in-memory SQLite with
  `RefreshDatabase`. It covers the request-level contracts: authorization, validation, workflow
  transitions, the single-follow-up and single-survey constraints, archive and restore, the
  last-administrator guard, login throttling, localization, eager loading and pagination.
- **A MySQL concurrency harness** at `tests/Concurrency` — 12 two-process scenarios that hold a
  row lock in one process and run the real controller action in another. It is not part of the
  Pest suite and does not run in CI; it requires a disposable database whose name must end in
  `_verify` and must differ from the application's own. Five runs on 2026-11-10 report 6 to 9
  failing checks each: two scenarios are outdated fixtures, one is the intermittent
  mutual-demotion race (OQ-12), and one timing assertion can report a false failure.
- **Pint** is configured and used. **No static analyser** is installed, so no Larastan-style
  gate exists.
- **No CI pipeline** is committed.

---

## 11. Delivery Roadmap

The modules were built in dependency order, and the order is visible in the migrations: each
step only introduces tables whose foreign keys already exist. Every step below is delivered.

| # | Step | What it produced | State |
|---|---|---|---|
| 1 | Foundation | Laravel 13 skeleton, Vite and Tailwind, Arabic locale and RTL layout, error pages | Done |
| 2 | Authentication | Username login, logout, two rate-limit buckets, session regeneration | Done |
| 3 | Roles and permissions | Two roles, 38 permissions, eight policies, seeded catalogue, sidebar gating | Done |
| 4 | Patients | Schema, MRN allocation, phone validation, register, profile, archive and restore | Done |
| 5 | Clinics and doctors | Department structure, doctor-to-clinic link, statuses, deletion guards | Done |
| 6 | Visits | Three-state lifecycle, completion-with-follow-up transaction, filters, freeze rules | Done |
| 7 | Follow-ups | Four-state machine, mandatory outcome note, overdue command and schedule | Done |
| 8 | Surveys | One per completed visit, low-rating transaction, read-only after creation | Done |
| 9 | Complaints | Two intake paths, five-stage workflow, assignment, reopening, statistics | Done |
| 10 | Dashboard | KPI aggregation service and administrator-only screen | Done |
| 11 | Assurance | 171 Pest tests, 12 concurrency scenarios, performance indexes, documentation | Done |

Two later corrections belong to the record because they changed behaviour rather than wording:
the follow-up outcome note moved from optional to mandatory, and the follow-up text columns were
widened from 255 to 500 in a forward migration so the rules and the schema agree.

---

## 12. Acceptance Criteria

Each criterion is stated as an observable outcome, and the Pest file that holds it is named. A
criterion with no file is enforced but not separately tested; §15 collects those.

- Registering a patient with a phone number already on file is refused, including when the
  existing file is archived. — `PatientCreationTest`
- Restoring an archived file keeps its original phone number reserved. — `PatientArchiveTest`
- Archiving a patient writes `deleted_by` and keeps the file out of the active register, the
  patient dropdowns and the profile screen; restoring clears the archive state. — `PatientArchiveTest`
- A visit can only be scheduled for a patient that exists and is not archived, and a
  completion-with-follow-up creates exactly one follow-up. — `VisitWorkflowTest`
- A completed visit refuses an edit of its date, doctor or reason and refuses deletion; a
  cancelled visit refuses an edit. — `VisitWorkflowTest`
- A second follow-up for the same visit is refused by the database. — `VisitWorkflowTest`
- Completing a follow-up without an outcome note is refused, and completing one that is not
  `scheduled` is refused. — `FollowUpWorkflowTest`
- A scheduled follow-up cannot be marked `missed` by hand, and the overdue command does mark it
  missed. — `FollowUpWorkflowTest`
- `done` is terminal: neither the edit form nor the status endpoint moves it again. — `FollowUpWorkflowTest`
- An overdue follow-up saves without its date moving, and a reschedule to a past date is
  refused. — `FollowUpWorkflowTest`
- A rating of 2 creates exactly one survey and one complaint with `source = survey`; a rating of
  4 creates the survey and no complaint. — `SurveyWorkflowTest`
- A second survey for the same visit is refused, and a survey on a visit that is not completed
  is not recorded. — `SurveyWorkflowTest`
- A survey that raised a complaint cannot be deleted. — `SurveyWorkflowTest`
- Advancing a complaint from `new` without an assignee is refused; resolving it without a
  resolution note is refused; closing a complaint that is already closed is refused. —
  `ComplaintWorkflowTest`
- Reopening a resolved or closed complaint returns it to `investigating` and clears both stage
  timestamps. — `ComplaintWorkflowTest`
- A complaint cannot be moved to a different patient, and it cannot be filed against an
  archived patient. — `ComplaintWorkflowTest`
- Assigning at creation is refused for an actor who does not hold `complaints.assign`. —
  `ComplaintWorkflowTest`
- A receptionist cannot reach the user screens, restore a patient file, delete an open visit, or
  advance a complaint that is not assigned to them. — `UserSecurityTest`, `PatientArchiveTest`,
  `VisitWorkflowTest`, `ComplaintWorkflowTest`
- The last administrator can be neither demoted nor deleted while another administrator remains
  the alternative (sequential operations; the mutual-demotion race is OQ-12). — `UserCrudTest`
- A user may not delete their own account. — `UserCrudTest`
- An account holding `users.create` but not the administrator role cannot create an
  administrator. — `UserSecurityTest`
- Five failed sign-ins lock the correct password out of the user bucket, and an unknown username
  returns the same message as a wrong password. — `LoginSecurityTest`
- A clinic with doctors cannot be deleted, even when every doctor is inactive. —
  `DoctorClinicDeletionTest`
- Guests are redirected to the login page from every protected route, and signed-in users are
  routed by role from the root path. — `HomeRedirectTest`
- The dashboard's satisfaction figures equal the aggregates of the stored ratings. —
  `SurveyStatsTest`
- The register pages issue one query per relation instead of one per row. — `EagerLoadingTest`
- Framework validation messages are returned in Arabic, not as translation keys. —
  `LocalizationTest`

---

## 13. Future Extension

None of the following exists in the code base. It is recorded so the boundary is explicit and so
the CRM's design decisions can be judged against where the product is meant to go.

- **The medical audit half.** Audit templates with weighted criteria; audit sessions assigned to
  an auditor; answers scored Compliant (1), Partial (0.5), Non-compliant (0) and Not applicable
  (excluded), with a weighted percentage score; a review workflow in which a manager distinct
  from the auditor finalises a session and locks the answers; and one corrective action per
  non-compliant answer, with `Open → In progress → Done → Verified` and an overdue flag from a
  scheduled scan.
- **Claims.** Insurance claims, rejections and rejection reasons.
- **A facility hierarchy.** The current `clinics` group doctors inside one organisation; a
  facility/department tree would replace that.
- **An activity log.** A separate append-only ledger of who changed what and when, beyond the
  `created_by` / `updated_by` columns.
- **Reporting.** Exports, period-based reports, and KPI definitions beyond the current
  all-time dashboard.
- **A REST API.** A versioned API with token authentication, if a mobile client is ever needed.
- **Notifications.** SMS, WhatsApp or email behind an interface, so a follow-up can reach the
  patient without a phone call from the desk.
- **Delivery engineering.** Docker, a CI pipeline that runs the Pest suite and the concurrency
  harness, and a static-analysis gate.
- **An English interface**, should the product ever need a second locale.

---

## 14. Assumptions and Open Questions

**Assumptions.**

- This document describes the application as it exists in this repository. Where prose
  elsewhere in the repository disagrees with the executable code, the code is authoritative and
  the disagreement is listed below.
- Patient feedback is collected by staff. There is no automated contact with the patient.
- The organisation is single. Clinics are departments, not tenants.
- The interface is Arabic and only Arabic; translation work means adding a locale, not switching
  one.
- Seed data is synthetic. No real patient or staff record is stored in this repository.

**Open questions.**

- **OQ-1 — Doctor deletion archives the row; it does not refuse it.** `DoctorService::delete()`
  marks the doctor `inactive` and soft-deletes it even when visits reference it, and the visits
  keep resolving the doctor through `withTrashed()`. The behaviour is defensible — the history is
  preserved — but the test named «refuses to delete a doctor who has visits» asserts only that the
  row still exists, which is true either way. The README was corrected on 2026-11-10 to describe
  the archive rather than a refusal.
- **OQ-2 — Two test names describe refusals that are actually soft deletes.** The same pattern
  appears in `UserCrudTest` («refuses to delete a user that authored records» asserts
  `assertSoftDeleted`). The behaviour is correct and intentional; the names invite a reader to
  conclude a guard exists where the protection comes from soft deletion instead. The README now
  states that users are archived and that the foreign keys are the backstop.
- **OQ-3 — The dashboard has no reporting period.** Every figure is all-time, while "visits this
  month" and "average satisfaction this month" are the metrics management usually asks for.
  Adding a period control is a product decision, not a defect.
- **OQ-4 — There is no activity log.** If a medical product must be able to answer "who changed
  this and when" beyond the last editor, §13's activity-log item moves from extension to
  requirement.
- **OQ-5 — Complaint stage wording differs by surface.** A badge reads «تم الحل والمعالجة», «قيد
  التدقيق والتحقيق», «مغلقة نهائياً», while the filter select and the lifecycle indicator use
  the short form «تم الحل», «قيد التحقيق», «مغلقة». `new` matches exactly. This is a wording
  preference, not a missing translation.
- **OQ-6 — Two role-label maps exist.** `lang/ar/roles.php` maps `Receptionist` to «موظف استقبال»,
  while `layouts/header.blade.php` carries its own array that maps it to «استقبال». The header
  should read the language file, or the file should adopt the shorter form.
- **OQ-7 — `patients.status` is effectively constant.** Registration always writes `active`, no
  flow changes it, and the register filters on `active`. The column and the `AccountStatusEnum`
  that describes it are therefore unused as a business concept; the real notion of an
  unavailable file is the archive. Either give the status a meaning or remove it.
- **OQ-8 — `doctors.phone` uniqueness is validation-only.** `CreateDoctorRequest` and
  `UpdateDoctorRequest` enforce it; the column carries no unique index, so any path that skips
  the form request could store a duplicate. Clinic names are not unique at all.
- **OQ-9 — The header looks up the signed-in user's role inside the Blade template.** It is one
  call for one label, not a query in a loop, but it is a data lookup that belongs in a view
  composer or a shared layout variable.
- **OQ-10 — `resources/views/welcome.blade.php` is unreferenced.** The root path redirects;
  nothing renders that view.
- **OQ-11 — The concurrency harness is not part of CI.** It needs a disposable MySQL database and
  a `CONCURRENCY_DB_DATABASE` value, and it is timing-based, so a slow machine reports a false
  failure rather than a false pass. That is the right direction for a failure, but it means the
  harness is run deliberately, not automatically.
- **OQ-12 — Two administrators demoting each other at the same moment can both pass.**
  `UserService::update()` locks the administrator rows and reads the count inside the transaction,
  but the count comes from a `whereHas('roles')` membership subquery, which does not appear to
  receive the same locking read — the likely mechanism. The harness reproduced the outcome in
  three of five runs
  on 2026-11-10: `admins_before: 2`, `admins_after: 0`. Which transaction commits first decides
  the outcome, so the race is intermittent rather than certain. Sequential demotions and deletions
  are refused correctly.
  Closing the race needs a locking read that MySQL actually serialises on the membership rows — a
  direct read of `model_has_roles` for the administrator role, or a dedicated counter row — and a
  test that can express two competing processes. The behaviour is not fixed here; the document
  records it and the harness keeps failing until it is.

---

## 15. Reconciliation with the shipped application

Reconciled on 2026-11-10 against `app/`, `database/migrations`, `routes/`, `resources/views` and
`tests/`. Each subsection takes one section of this document, checks it against the code that
implements it, and states what the code does wherever the document could be read more broadly
than the code allows. "Shipped" means present in this repository. Nothing here is a proposal;
every item marked **missing** is reported, not planned.

| Section | Checked against |
|---|---|
| §2 Actors | `PermissionEnum`, `RolePermissionSeeder`, all eight policies, `routes/web.php` middleware |
| §3 Scope | The route table, the controllers, the views, and the migration list |
| §4 Workflow | `VisitService`, `SurveyService`, `ComplaintService`, `FollowUpService`, the controllers' flash contracts |
| §5 State machines | `FollowUpService::TRANSITIONS`, `Complaint::nextStatus()` / `canReopen()`, `VisitService::changeStatus()` |
| §6 Business rules | Every rule traced to the class or index that enforces it, as named in the table |
| §7 Requirements | Controllers, services, query objects, and the screens that render each result |
| §8 Data model | `database/migrations`, read in order, including both alter migrations |
| §9 Screens | `resources/views` and the sidebar |
| §10 Technical design | `composer.json`, `package.json`, `config/app.php`, the service and query directories, `routes/console.php` |
| §11 Roadmap | Migration timestamps and the dependency order they encode |
| §12 Criteria | The Pest suite: 171 tests, 612 assertions, all passing on in-memory SQLite |
| §16 Interface | `resources/views`, `lang/ar`, and a sweep of all 58 templates |

### 15.1 Headline differences

The facts a reader should hold before reading §1 to §14 as a description of this application.

| Topic | The reading this document replaces | What this application does |
| --- | --- | --- |
| Framework | Laravel 11 | Laravel 13.34 on PHP 8.5 (`composer.json`) |
| Admin UI | An admin-panel package | Hand-built Blade + Tailwind CSS v4, Arabic RTL; no panel package |
| Audit trail | `spatie/laravel-activitylog` | Not installed. Attribution comes from `created_by` / `updated_by` / `deleted_by` on the domain tables, with `withTrashed()` relations (§15.7, FR-8) |
| Static analysis | Larastan | Not installed. Pint only |
| Visit | Recorded as already completed | A real state machine: `created → completed` or `created → cancelled`, frozen after either (§15.5) |
| Organisation | Not modelled | `clinics` and `doctors`, with statuses, a schedule, and a doctor→clinic foreign key |
| Patient identifier | Not modelled | `patients.mrn`, unique across archived files, issued by the service on registration |
| Follow-up scanning | A daily job | `followups:update-overdue`, scheduled **every minute** with `withoutOverlapping()` |
| Status values | Backed enums owning their transitions | Plain strings on models and services. `FollowUpService::TRANSITIONS` holds the follow-up table; `Complaint::nextStatus()` holds the complaint one |
| Events, listeners, jobs | Optional events on survey and status changes | `app/Events`, `app/Listeners` and `app/Jobs` do not exist |
| Interface language | English | Arabic only, RTL, `Africa/Cairo` |

**What this document deliberately does not claim.**

- **It does not claim a separate audit trail.** FR-8 states the opposite, and §13 records an
  activity log as an extension.
- **It does not claim period-based reporting.** Every dashboard figure is all-time (OQ-3).
- **It does not claim a green CI pipeline.** There is none.
- **It does not claim that deletion is used for any clinical record.** Patients, doctors and
  staff are soft-deleted; only visits, follow-ups, surveys and complaints are removed, and each
  has its own guard.
- **It does not claim that the audit or claims modules exist.** §13 is explicitly forward-looking.
- **It does not claim that the concurrency harness passes.** The Pest suite was run on
  2026-11-10 and passes in full. The harness was also run on 2026-11-10 against a disposable
  MySQL database, five times: 12 scenarios and 64 checks per run, with 6 to 9 failing checks.
  Two scenarios fail every time because the fixtures are outdated (`mrn_collision_race`,
  `followup_status_together`); `last_admin_demote_together` failed in three of the five runs
  (OQ-12); one timing check in `visit_destroy_vs_complete` reported a false failure once.

### 15.2 Actors (§2)

Implemented: two roles (`RoleEnum`) and 38 permissions (`PermissionEnum`). `RolePermissionSeeder`
gives the administrator all 38 and the receptionist 16 — the 22 excluded are the four user
permissions, every write permission on clinics and doctors, `patients.delete`, `visits.update`,
`visits.delete`, `followups.update`, `followups.delete`, `surveys.update`, `surveys.delete`, and
the five complaint permissions other than view and create. Eight policies enforce the catalogue,
one per domain model.

Two capabilities are **wider or narrower than the permission that names them**, and the document
states both where it matters rather than leaving them to the code:

- `ComplaintPolicy::advance()` grants the action to any holder of `complaints.advance` **or** to
  the assignee of a complaint whose status is neither `new`, `resolved` nor `closed`. This is the
  one place where an actor reaches a stage without the permission for it.
- `GET /dashboard` carries `role:Admin` in addition to `auth`, and the sidebar renders the link
  under `@role('Admin')`; the dashboard is therefore not reachable by permission alone.

There is **no patient-facing portal**, as §1.2 states: no patient login, no token, no portal
route.

### 15.3 Scope (§3)

The eight scope steps this analysis has always taken, and their status in the current
application:

| # | Step | Status |
| --- | --- | --- |
| 1 | Authentication and roles | Implemented — username and password, two roles, 38 permissions |
| 2 | Patients management | Implemented, and extended — MRN, blood group, allergies, chronic conditions, emergency contact, archive and restore |
| 3 | Visits | Implemented, and extended into a state machine linked to a doctor and, through the doctor, a clinic |
| 4 | Follow-ups | Implemented — and the outcome note is now **required**, where it was once optional |
| 5 | Satisfaction surveys | Implemented — one per completed visit |
| 6 | Complaints | Implemented — automatic from a survey, or manual |
| 7 | Dashboard with KPIs | Implemented — administrator-only, KPI cards plus workflow tables |
| 8 | Activity log for all important state changes | **Not implemented** — the step is answered differently by FR-8; see OQ-4 |

Scope the current application adds that the eight steps did not contain: **clinics**,
**doctors**, **visit statuses**, **MRN allocation**, **archive and restore** for patients and
doctors, **login throttling**, **branded error pages**, and the **MySQL concurrency harness**.

### 15.4 Core workflow (§4)

The journey matches §4.1 and §4.2 step for step: register → schedule → complete or cancel →
follow-up worked to `done` → survey on the completed visit → a rating below 3 opens a complaint
in the same transaction → the complaint moves through its stages.

Two details are easy to read past, and §4 states them explicitly:

- The visit names a **doctor record**, not a free-text doctor name, and reaches the clinic
  through that record. A visit cannot be created for a doctor who does not exist.
- The visit is created as `created` and must then be **completed** or **cancelled**. It is not
  born completed, and completion freezes its date, doctor, reason and patient.

### 15.5 State machines (§5)

**Follow-up.** §5.1 is the table in `FollowUpService::TRANSITIONS`, reproduced exactly. Three
points of that table are worth restating because a reader could assume otherwise:

- **`missed → done` is not available.** A missed follow-up must return to `scheduled` before it
  can be completed. This is the one transition a "patient came late" narrative would expect and
  the code does not provide.
- **`missed → cancelled` and `cancelled → scheduled` are available.** Both are wider than a
  minimal reading of the lifecycle, and both are deliberate: a missed follow-up may be closed
  out, and a cancelled one may be revived against a future date.
- **`done` is terminal at three separate enforcement points** — `complete()`, `changeStatus()`
  and `update()` — not only at the status endpoint.

**Complaint.** §5.2 matches `Complaint::nextStatus()` exactly: `new → assigned → investigating →
resolved → closed`, with `canReopen()` true for `resolved` and `closed`. `closed` has no forward
edge. The resolution note is validated inside the same locked transaction that writes the status,
so the check and the write cannot be separated by a competing request.

**Visit and survey.** §5.3 matches `VisitService`: `created → completed | cancelled`, with
`update()` refusing both closed statuses, `delete()` refusing `completed`, and the policy refusing
`completed` a second time before the service is reached. The survey has **no edit route at all** —
`index`, `store`, `show`, `destroy` — which is what makes the satisfaction figures worth reading,
and `delete()` refuses a survey that raised a complaint.

### 15.6 Business rules (§6)

Every rule in §6 traced to what enforces it, and to the test that covers it:

| Rule | Enforced in | Automated coverage |
|---|---|---|
| BR-1 | unique indexes, `BasePatientRequest`, `PatientService::register()` | `PatientCreationTest`, `PatientArchiveTest` |
| BR-2 | `CreateVisitRequest`, foreign keys | `VisitWorkflowTest` |
| BR-3 | `VisitService::create()`, `CreateVisitRequest` | `VisitWorkflowTest` |
| BR-4 | `UpdateFollowUpRequest::rescheduleRule()` | `FollowUpWorkflowTest` |
| BR-5 | `follow_ups.visit_id` unique, `VisitService::changeStatus()` | `VisitWorkflowTest`, `FollowUpWorkflowTest` |
| BR-6 | `surveys.visit_id` unique, `SurveyService::submit()` | `SurveyWorkflowTest` |
| BR-7 | `CreateSurveyRequest` | `SurveyWorkflowTest` |
| BR-8 | `SurveyService::submit()`, `complaints.survey_id` unique | `SurveyWorkflowTest` |
| BR-9 | `ComplaintService::advance()` inside the row lock | `ComplaintWorkflowTest` |
| BR-10 | `ComplaintService::assign()`, `AssignComplaintRequest`, `ComplaintService::create()` | `ComplaintWorkflowTest` |
| BR-11 | `UpdateFollowUpStatusRequest`, `FollowUpService::TRANSITIONS` | `FollowUpWorkflowTest` |
| BR-12 | `FollowUpService::complete()`, `changeStatus()`, `update()` | `FollowUpWorkflowTest` |
| BR-13 | `VisitService::update()` / `delete()`, `VisitPolicy` | `VisitWorkflowTest` |
| BR-14 | `FollowUpService::update()` | `FollowUpWorkflowTest` |
| BR-15 | `UpdateComplaintRequest::immutablePatientRule()` | `ComplaintWorkflowTest` |
| BR-16 | `SurveyService::delete()` | `SurveyWorkflowTest` |
| BR-17 | `ClinicService::delete()` under a row lock | `DoctorClinicDeletionTest` |
| BR-18 | `DoctorService::delete()`, `SoftDeletes`, `withTrashed()` | `DoctorClinicDeletionTest`, partially — see OQ-1 |
| BR-19 | `PatientService::archive()` / `restore()` | `PatientArchiveTest` |
| BR-20 | `UserService::lockAdministrators()` | `UserCrudTest` covers the sequential refusals; concurrency scenario `last_admin_delete_together` passes and `last_admin_demote_together` fails (OQ-12) |
| BR-21 | `SoftDeletes` on `User`, `withTrashed()` relations | `UserCrudTest` |
| BR-22 | `UserController::destroy()`, `UserService::create()` | `UserCrudTest`, `UserSecurityTest` |
| BR-23 | Services and controllers passing `Auth::id()` | `ComplaintWorkflowTest`, `ClinicCreationTest` |
| BR-24 | `LoginRequest`, `AuthController` | `LoginSecurityTest` |
| BR-25 | `role:Admin` middleware | none dedicated |
| BR-26 | `lockForUpdate()` in six services | the 12 concurrency scenarios, outside the Pest suite; the administrator-count exception is OQ-12 |

### 15.7 Functional requirements (§7)

FR-1 to FR-8 keep their positions and their purposes. Two changes belong to the record:

- **FR-8** is the step that described an activity log. No log exists, so the step is answered by
  the attribution columns and the `withTrashed()` relations instead of being dropped. A reader
  who needs a real ledger is directed to OQ-4 and §13.
- **FR-9 and FR-10** were appended for clinics and doctors, which the original eight steps did
  not contain. They were appended rather than inserted so that no earlier step was renumbered.

What enforces each step: FR-1 and FR-2 by `AuthController`, `LoginRequest`, `UserService` and
`PatientService`; FR-3 to FR-6 by the four workflow services under row locks; FR-7 by
`DashboardService::summary()`; FR-8 by the `*_by` columns and their relations; FR-9 and FR-10 by
`ClinicService` and `DoctorService` with their caches and deletion guards.

### 15.8 Data model (§8)

§8 was written from `database/migrations` read in dependency order. Four points are load-bearing
and are stated in §8 rather than left in the schema:

- The one-follow-up and one-survey constraints are **unique indexes**, not only service checks,
  so no code path can violate them.
- `complaints.survey_id` is unique *and* nullable, and the link runs survey → complaint. There is
  no `complaint_id` on `surveys`.
- Two later migrations changed behaviour rather than wording: `2026_10_28_094306` added the
  performance indexes, and `2026_11_03_084614` widened `follow_ups.reason` and
  `follow_ups.outcome_note` from 255 to 500 so that the validation length and the column length
  agree.
- `patients.deleted_at` is indexed explicitly, because soft deletes alone do not index it and the
  archived list depends on it.

Two gaps are recorded as open questions rather than smoothed over: `doctors.phone` is unique only
in the form requests, with no unique index behind it (OQ-8), and `patients.status` is written as
`active` on every path with no flow that changes it (OQ-7).

### 15.9 Screens (§9)

Verified against all 58 templates in `resources/views`. There is no admin-panel package, so every
screen in §9 is a Blade view with its own controller action. Three screens are worth calling out
because their absence is deliberate: **surveys have no create screen** (recording happens in the
modal on the visits register and the dashboard), **users have no detail screen**, and **there is
no activity-log screen** — the step does not exist. All five error screens render from the single
`x-error-page` component, and the sidebar collapses to a drawer with an overlay below the `lg`
breakpoint.

### 15.10 Technical design (§10)

§10 was written from `composer.json`, `package.json`, `config/app.php`, the service and query
directories, and `routes/console.php`. One departure from the design this document replaces is
worth naming: the plan called for **Action classes**; the application uses **Services** for the
workflow and adds **query objects** (`app/Queries`) for the read paths, six of them. The layering
is otherwise as §10.1 draws it, with controllers holding no business rules.

The enums are partly load-bearing and partly not, and §10.1 says which: `RoleEnum` and
`PermissionEnum` drive roles, policies and middleware; `GenderEnum` and `BloodGroupEnum` are used
by the factories; `AccountStatusEnum` is declared and referenced nowhere (OQ-7).

### 15.11 Acceptance criteria (§12)

The criteria in §12 are the Pest suite's own contracts. Measured twice on 2026-11-10: **171
tests, 612 assertions, all passing** in 94,556 ms and in 140,919 ms, against in-memory SQLite
with `RefreshDatabase`; the counts are stable, the wall time tracks machine load. Two
criteria in §6 carry no dedicated test: BR-25 (the dashboard's `role:Admin` middleware) is
enforced but untested, and BR-26 (the row locks) is covered by the concurrency harness rather
than by the Pest suite, which cannot express two competing processes.

The suite is deliberately narrower than the harness: it proves the contracts, and the harness
exercises the locks. Its five runs on 2026-11-10 show the visit, follow-up and complaint locks
holding, the administrator guard failing intermittently when two administrators demote each other
at the same time (OQ-12), and the MRN scenario stuck on an outdated phone fixture before it
reaches the lock.

### 15.12 Roadmap (§11)

§11's order is the migration order, which is the dependency order: each step introduces only
tables whose foreign keys already exist. Every step in it is delivered; there is no partial or
abandoned step. The two forward corrections recorded there — the outcome note becoming mandatory,
and the follow-up text widening — are the only changes that altered behaviour after a step was
complete.

### 15.13 Future extension (§13)

Nothing in §13 exists in this repository. Verified by absence: no audit or claim tables in
`database/migrations`, no `app/Events`, `app/Listeners` or `app/Jobs`, no API route file, no
Dockerfile, no CI workflow, no notification channel, and no second locale under `lang/`. §13 is
written so the boundary is explicit, not as a plan of record.

### 15.14 Open questions (§14)

The twelve open questions in §14 divide into four kinds, and the distinction matters when
deciding what to do about them:

- **Verified behavioural facts, where prose elsewhere disagrees** — OQ-1 and OQ-2. The code is
  correct and the wording is wrong; the fix is editorial.
- **Product decisions, not defects** — OQ-3 (reporting period), OQ-4 (activity log), OQ-5
  (stage wording).
- **Unused or under-enforced schema and presentation** — OQ-6, OQ-7, OQ-8, OQ-9, OQ-10, OQ-11.
- **A real concurrency defect** — OQ-12. The last-administrator guard does not hold against two
  simultaneous mutual demotions, and the harness reproduces it. This one changes what the
  application can do today: it can be left without an administrator.

§15.1 lists what this document does not claim, and §14's assumptions are the ones this analysis
was written under.

---

## 16. UI, translation and Blade review

Reviewed on 2026-11-10 against the templates in `resources/views` and the language files in
`lang/ar`. This section is the presentation audit: developer vocabulary reaching the screen,
translation coverage, Blade architecture, Arabic RTL quality, and what cannot be closed from the
view layer. It records three different things and never mixes them: **what the templates
contain**, **what was found and left alone**, and **what needs a backend change first**.

The review is read-only. No controller, service, model, form request, policy, gate, middleware,
route, migration, seeder, view or configuration file was modified for this analysis: the brief
for this document restricted the change to the document itself. Every finding below is therefore
reported rather than corrected, and §16.6 says so explicitly.

### 16.1 What the review covered

The presentation layer only: all 58 templates under `resources/views`, the two layouts
(`app`, `header`), the sidebar, the 15 shared components, and the Arabic language files. The
questions asked of each were the same four: does any developer vocabulary reach the screen, is
any text untranslated, does the view compute or query anything it should not, and does any
control imply a permission its route does not require.

### 16.2 Internal identifiers that were shown to users

**Requirement identifiers.** `grep -rn -E 'FR-[0-9]|BR-[0-9]' resources/views` returns nothing.
The numbering lives in this document and nowhere in served markup. This includes HTML comments,
which are served to the browser even though they are not rendered.

**Status and source tokens.** No template prints a raw stored value into a badge. The four
workflow models expose label accessors — `Visit::getStatusLabelAttribute`,
`FollowUp::getStatusLabelAttribute`, `Survey::getRatingLabelAttribute`,
`Complaint::getStatusLabelAttribute` and `Complaint::getSourceLabelAttribute` — and the templates
render those instead. A reader of the register sees «مكتملة بنجاح», «فائتة / غير مجابة» or
«استبيان رضا (تلقائي)», never `completed`, `missed` or `survey`.

**Table and column names.** A sweep for `DB::`, `::where(`, `::all()`, `::get()`, `::pluck(`
and `::count()` across `resources/views` returns nothing, so no template can leak a schema name
through a query it wrote itself.

### 16.3 Translations

- The layout declares `lang="ar"` and `dir="rtl"`, and the interface is Arabic throughout. There
  is no language switcher, and `APP_LOCALE=ar` with an Arabic fallback, time zone `Africa/Cairo`.
- `lang/ar/` carries `auth.php`, `pagination.php`, `passwords.php`, `roles.php` and
  `validation.php` (15.5 KB), so a framework validation error is a sentence rather than a
  translation key. `LocalizationTest` covers this.
- Every form request ships its own Arabic messages, including the rules the framework file cannot
  know about: the MRN retry failure, the future follow-up date, the last-administrator refusal,
  the immutable complaint patient.
- `lang/ar/roles.php` is the only application-level translation file. Role names are stored in
  Latin and rendered through `User::roleLabels()`, which resolves a label and falls back to the
  stored name for an unmapped role.
- **One gap**, recorded as OQ-6: `layouts/header.blade.php` carries its own inline map that
  renders `Receptionist` as «استقبال» where the language file and `User::roleLabels()` say «موظف
  استقبال». The same user is labelled two ways depending on which surface prints the role.

### 16.4 Text that was kept deliberately

Not everything Latin on screen is a defect. These were checked and kept:

- **Clinical abbreviations.** `MRN` / «الملف», blood groups (`O+`), the `Phone::international()`
  format, and the stage timestamps are vocabulary a clinic clerk reads every day. Spelling them
  out would be the regression.
- **The source qualifier on complaints.** «استبيان رضا (تلقائي)» and «تسجيل يدوي (مباشر)» are
  descriptive interface text — they say where a record came from. They are not a second name for
  a state, and the bracket is not an English gloss.
- **Decorative glyphs.** Emoji and symbol characters appear in 37 of the 58 templates: 🏥 ⭐ 🚨 👤
  on the dashboard KPI cards, ⚠ in the delete confirmations, ★ ☆ for ratings, ✓ ✕ on dialog
  actions, ➜ ← as RTL navigation arrows. They are not untranslated text and they are not
  identifiers. Whether they suit a clinical product is a design decision (OQ-5 records the one
  place where wording genuinely differs between surfaces).

### 16.5 Blade architecture — findings

- **No database access in the templates.** The sweep in §16.2 returns nothing.
- **No calculation in the templates.** No `@php` block holds a loop, a sort or an aggregate. The
  dashboard's rates come from `DashboardService::summary()`, so a figure and the rate derived from
  it cannot disagree; the workflow aggregates come from the query objects. What remains in the
  templates is `@foreach`, `@if`, `@can`, and the per-row id derivation the dialog components
  need.
- **Shared components carry the repeated markup**: `x-icon` (27 usages across 14 templates),
  `x-sidebar-link` and `x-sidebar-section` for navigation, `x-alerts` for feedback, `x-error-page`
  for all five error screens, `x-archived-badge` (10 call sites), `x-visits.table`,
  `x-complete-followup-modal`, `x-complete-visit-with-followup-modal`, `x-create-survey-modal`,
  and one delete dialog per model.
- **Two KPI card components exist.** `x-dashboard.kpi-card` renders the dashboard's six figures;
  `x-kpi-card` is used by `users/index` alone. They are not interchangeable — their props differ
  (`subvalue` versus `subtitle`/`trend`) — so the same visual idea is expressed twice.
- **`x-dashboard.chart-card` is referenced by no template.** No screen in the application draws a
  chart.
- **One data lookup remains in a view**: `layouts/header.blade.php` resolves the signed-in user's
  role to print one label (OQ-9).
- **Table markup closes correctly.** The follow-ups register, the last one that had the problem,
  closes `</tbody>` before `</table>` and renders its row dialogs after the table rather than
  inside the table body.

### 16.6 Defects found

No template defect was found that shows a user the wrong thing or lets them attempt something the
server would refuse. The findings of this review are duplication, dead markup and wording —
recorded in §16.5 and §16.8 — plus the two documentation-and-behaviour mismatches that are not
view defects at all and are tracked as OQ-1 and OQ-2.

None of them was fixed, because this pass was restricted to this document. They are listed so the
next pass does not have to rediscover them, and §16.9 separates the ones that are a view-only
change from the ones that need a backend change first.

### 16.7 Authorization presentation — never weakened

Presentation never substitutes for authorization, and nothing in the templates is the only thing
standing between a user and an action. Every mutating control is wrapped in a `@can` / `@role`
pair asking the same policy question the route asks, and the route asks it again on the server,
so removing a template guard would expose a control that fails on submission rather than an
action that succeeds.

Three consequences are visible in the markup:

- The staff section of the sidebar and the dashboard link render only for a user who may use
  them; `GET /dashboard` carries `role:Admin` independently.
- A row whose action the viewer may not perform renders without that control, rather than with a
  control that would be refused.
- The clinic detail table keeps every cell present for an unauthorized viewer, replacing only the
  link with an em dash, so the row never shifts against its header.

### 16.8 Discovered but not fixed

- The duplicated role-label map and the resulting two names for one role (§16.3, OQ-6).
- The header's role lookup inside the template (OQ-9).
- The two KPI card components (§16.5).
- `x-dashboard.chart-card`, used by no template (§16.5).
- `resources/views/welcome.blade.php`, referenced by nothing — the root path redirects (OQ-10).
- Decorative glyphs across 37 templates, left alone by decision (§16.4).
- The complaint badge-versus-filter wording difference for three stages (OQ-5).

### 16.9 Items that would need a backend change first

| # | Item | What it needs |
|---|---|---|
| 1 | Resolve the header's role label through the language file in a view composer | A service-provider change, not a template edit (OQ-9) |
| 2 | Label the receptionist role one way everywhere | Either `lang/ar/roles.php` adopts the short form or the header reads it (OQ-6) |
| 3 | A reporting-period control on the dashboard | `DashboardService` must accept a period; the views cannot compute it (OQ-3) |
| 4 | A screen for the audit trail | The log itself does not exist (OQ-4, §13) |
| 5 | Consolidate the two KPI card components | A view change, but it changes the props `users/index` passes, so the index's data contract moves with it |
| 6 | Remove `welcome.blade.php` and `chart-card.blade.php` | A deletion decision for the maintainer, not a fix |