# Mini Patient CRM (Clinic Care Tracker) — Analysis & Requirements

> Purpose: single source of truth for building the project. Written to be used as context for Claude Code.
> Stack: Laravel 11, MySQL or PostgreSQL, Filament (admin UI), Pest.

---

> **Reconciliation status — 2026-11-08.** This file is the *original specification*, kept as design
> history. The application that was actually built differs from it in several important ways: it runs
> on **Laravel 13 with a hand-built Blade + Tailwind CSS UI, not Laravel 11 with Filament**; it adds
> clinics, doctors, a visit state machine and MRNs that this document never describes; and it omits
> several items specified here (the activity log, the "Missed → Done" transition, Filament resources).
> **Section 15 at the end reconciles every requirement, business rule and transition below with the
> shipped code.** Read section 15 before treating anything in sections 1–14 as implemented.

---

## 1. Overview

A small CRM that helps a clinic track its patients after they visit: register patients, record visits, schedule and track follow-ups, collect satisfaction ratings, and handle complaints. A dashboard gives management a quick view of how the clinic is doing.

This is a scaled-down portfolio/demo project inspired by the "Medical CRM" side of a healthcare technology company. It is built to demonstrate Laravel skills (clean architecture, state machines, roles, scheduled jobs, tests), not to be a full production system.

### 1.1 Goals
- Never lose a patient: every visit can have a follow-up, and missed ones are visible.
- Measure satisfaction and react automatically to unhappy patients.
- Give management a clear dashboard (KPIs).
- Show professional-grade engineering: services, enums, policies, transactions, tests, activity log.

### 1.2 Non-goals (MVP)
- No patient-facing login or portal. The patient never opens the system.
- No real SMS/WhatsApp/email sending (can be added later behind an interface).
- No medical records, billing, insurance, or appointments calendar.
- No multi-clinic / multi-tenant support.

---

## 2. Actors

| Actor | Uses the system? | What they do |
|---|---|---|
| **Receptionist** | Yes, all day | Registers patients, records visits, schedules and updates follow-ups, records survey answers given by the patient, opens complaints manually |
| **Admin / Clinic Manager** | Yes, for oversight | Everything the receptionist can do, plus manages users, views full dashboard, assigns and closes complaints |
| **Patient** | No | Only a source of information. Gives feedback verbally/by phone/message; staff enter it into the system |

Complaint handling in the MVP: any staff user can be assigned a complaint; Admin assigns and closes.

---

## 3. MVP Scope

1. Authentication and roles (Admin, Receptionist)
2. Patients management
3. Visits
4. Follow-ups
5. Satisfaction surveys (entered by staff)
6. Complaints (auto-created from low ratings, or manual)
7. Dashboard with KPIs
8. Activity log for all important state changes

---

## 4. Core Workflow

### 4.1 Patient journey (end to end)

```
Register patient
      |
      v
Record visit (status: completed)
      |
      +--> Schedule follow-up (optional, date + reason)
      |         |
      |         v
      |    Follow-up: Scheduled --> Done | Missed (auto) | Cancelled
      |
      v
Record satisfaction survey for the visit (rating 1-5 + optional comment)
      |
      +-- rating >= 3 --> Satisfied (counted in KPIs only)
      |
      +-- rating <  3 --> Complaint is auto-opened
                              |
                              v
                    New --> Assigned --> Investigating --> Resolved --> Closed
```

### 4.2 Step-by-step description

1. **Register patient.** Receptionist enters name, phone, gender, birth date, notes. Phone must be unique.
2. **Record visit.** Receptionist records a visit for an existing patient with date, doctor name, and visit type/reason.
3. **Schedule follow-up.** From the visit, the receptionist can set a follow-up date and a reason. A visit may have zero or one follow-up in the MVP.
4. **Follow-up outcome.**
   - Patient comes or is contacted: receptionist marks it **Done** (with an outcome note).
   - Patient cancels or it is no longer needed: marks it **Cancelled**.
   - Date passes and it is still Scheduled: a **daily job** flips it to **Missed**.
5. **Satisfaction survey.** After the visit, staff ask the patient for a rating from 1 to 5 and an optional comment, and record it. One survey per visit.
6. **Low rating reaction.** If rating is below 3, the system automatically opens a **Complaint** linked to that survey, status New.
7. **Complaint handling.** Admin assigns it to a staff member. The assignee investigates, then resolves it with a mandatory resolution note. Admin closes it.
8. **Dashboard.** Management sees counts and rates updated from all of the above.

---

## 5. State Machines

### 5.1 Follow-up

| From | To | Who / trigger | Rule |
|---|---|---|---|
| Scheduled | Done | Staff | Outcome note required |
| Scheduled | Cancelled | Staff | Reason note required |
| Scheduled | Missed | Daily scheduled command | Only if `due_date < today` |
| Missed | Done | Staff | Allowed (patient came late); outcome note required |

Done and Cancelled are final.

### 5.2 Complaint

| From | To | Who | Rule |
|---|---|---|---|
| New | Assigned | Admin | Must choose an assignee |
| Assigned | Investigating | Assignee | |
| Investigating | Resolved | Assignee | `resolution_note` required |
| Resolved | Closed | Admin | Only after Resolved |
| Resolved | Investigating | Admin | Reopen with a reason if resolution is not acceptable |

A complaint cannot move to Resolved or Closed without a resolution note.

### 5.3 Visit and Survey
- Visit has no workflow in the MVP; it is recorded as completed.
- A survey is created once per visit and is not editable after creation (to keep KPIs trustworthy). Admin may delete in case of data-entry error (logged).

---

## 6. Business Rules

- **BR-1:** Patient phone number is unique.
- **BR-2:** A visit must belong to an existing patient.
- **BR-3:** A follow-up due date cannot be earlier than the visit date.
- **BR-4:** One survey per visit (unique constraint on `visit_id`).
- **BR-5:** Survey rating must be an integer from 1 to 5.
- **BR-6:** Rating below 3 automatically creates exactly one complaint linked to that survey (idempotent; never create duplicates).
- **BR-7:** Complaint cannot be Resolved or Closed without `resolution_note`.
- **BR-8:** Follow-up becomes Missed only through the daily job, never manually.
- **BR-9:** Every state transition is written to the activity log (who, when, from, to).
- **BR-10:** Receptionists cannot manage users and cannot close complaints.
- **BR-11:** Patients are soft-deleted, never hard-deleted, to keep history.
- **BR-12:** Auto-created complaint is created inside the same DB transaction as the survey.

---

## 7. Functional Requirements

### FR-1 Authentication and roles
- Login/logout; roles Admin and Receptionist (Spatie Permission).
- Admin can create/deactivate users.

### FR-2 Patients
- Create, edit, view, search (name/phone), soft-delete.
- Patient profile shows visits, follow-ups, surveys, and complaints in one place.

### FR-3 Visits
- Record visit with date, doctor name, reason.
- List and filter visits by date range and patient.

### FR-4 Follow-ups
- Create from a visit; fields: due date, reason, status, outcome note.
- "Today's follow-ups" and "Missed follow-ups" lists for the receptionist.
- Daily job marks overdue Scheduled follow-ups as Missed.

### FR-5 Satisfaction surveys
- Record rating (1-5) and comment for a visit.
- Triggers BR-6.

### FR-6 Complaints
- Auto-created from low rating or created manually (source: `survey` or `manual`).
- Assign, investigate, resolve, close, reopen, per section 5.2.
- Filter by status and assignee.

### FR-7 Dashboard
KPIs:
- Total patients; new patients this month
- Visits this month
- Follow-ups due today
- Missed follow-ups (count and list)
- Follow-up completion rate = Done / (Done + Missed) over a selected period
- Average satisfaction rating over a selected period
- Open complaints by status; average time to resolve

### FR-8 Activity log
- Log creation and every state change of follow-ups and complaints (Spatie Activitylog).
- Viewable by Admin.

---

## 8. Data Model

```
users
  id, name, email, password, is_active, timestamps
  (roles via Spatie)

patients
  id, full_name, phone (unique), gender, birth_date, notes,
  timestamps, deleted_at

visits
  id, patient_id (FK), visit_date, doctor_name, reason,
  created_by (FK users), timestamps

follow_ups
  id, visit_id (FK), patient_id (FK), due_date,
  status (scheduled|done|missed|cancelled),
  reason, outcome_note, completed_at,
  created_by (FK users), timestamps

surveys
  id, visit_id (FK, unique), patient_id (FK),
  rating (1-5), comment,
  recorded_by (FK users), timestamps

complaints
  id, patient_id (FK), survey_id (FK, nullable, unique),
  source (survey|manual),
  title, description,
  status (new|assigned|investigating|resolved|closed),
  assigned_to (FK users, nullable),
  resolution_note, resolved_at, closed_at,
  created_by (FK users, nullable), timestamps
```

Relationships:
- Patient has many Visits, FollowUps, Surveys, Complaints.
- Visit belongs to Patient; has zero or one FollowUp; has zero or one Survey.
- Survey has zero or one Complaint.
- Complaint belongs to a Patient and optionally to a Survey; assigned to a User.

Indexes: `patients.phone`, `follow_ups(status, due_date)`, `complaints(status, assigned_to)`, `surveys.visit_id`.

---

## 9. Screens (Filament resources/pages)

1. Login
2. Dashboard (KPI cards, missed follow-ups table, open complaints table, rating trend chart)
3. Patients: list, create/edit, profile view with tabs (Visits, Follow-ups, Surveys, Complaints)
4. Visits: list, create (from patient profile or standalone)
5. Follow-ups: list with quick filters (Today, Missed, Scheduled), actions: Mark done, Cancel
6. Surveys: create from visit, list
7. Complaints: list, detail with transition actions (Assign, Start, Resolve, Close, Reopen)
8. Users (Admin only)
9. Activity log (Admin only)

---

## 10. Technical Design

### 10.1 Architecture
- **Enums** (backed, string): `FollowUpStatus`, `ComplaintStatus`, `ComplaintSource`, `Gender`. Each enum owns its allowed transitions (`canTransitionTo()`).
- **Service/Action classes** hold business logic, not controllers or Filament pages:
  - `RecordSurveyAction` (creates survey and, if rating < 3, the complaint, in one transaction)
  - `MarkFollowUpDoneAction`, `CancelFollowUpAction`
  - `AssignComplaintAction`, `ResolveComplaintAction`, `CloseComplaintAction`, `ReopenComplaintAction`
- **Policies** for each model, mapped to roles.
- **Form Requests** for validation (used by actions/Filament forms).
- **Scheduled command** `followups:mark-missed`, runs daily.
- **Events/Listeners** optional: `SurveyRecorded`, `ComplaintStatusChanged`.

### 10.2 Packages
- `filament/filament` — admin UI
- `spatie/laravel-permission` — roles
- `spatie/laravel-activitylog` — audit trail
- `pestphp/pest` — tests
- `larastan/larastan`, `laravel/pint` — static analysis and code style

### 10.3 Quality bar
- Seeders and factories for all models, with realistic demo data.
- Pest feature tests for every business rule (BR-1 to BR-12) and every state transition, including invalid ones.
- Test for the daily job (idempotent, only affects overdue Scheduled).
- Test for duplicate prevention of auto-created complaints.
- Larastan at a reasonable level and Pint passing.
- README with setup steps, screenshots, and workflow diagram.

---

## 11. Implementation Roadmap

1. **Setup:** Laravel project, DB, Filament, Spatie packages, Pint/Larastan/Pest, README skeleton.
2. **Auth and roles:** users, roles, permissions, seed admin and receptionist.
3. **Patients:** migration, model, factory, Filament resource, search, soft delete.
4. **Visits:** migration, model, resource, link from patient profile.
5. **Follow-ups:** enum with transitions, actions, resource, today/missed filters, daily job.
6. **Surveys and auto-complaint:** `RecordSurveyAction` with transaction and idempotency.
7. **Complaints:** enum with transitions, actions, resource with transition buttons, policies.
8. **Activity log:** log all transitions; Admin view.
9. **Dashboard:** KPI widgets and charts.
10. **Tests, polish, docs:** complete test coverage, seeders for demo, README and screenshots.

Estimated: 2 to 3 weeks part-time.

---

## 12. Acceptance Criteria (samples)

- Creating a survey with rating 2 creates exactly one complaint with status New and source `survey`; creating one with rating 4 creates none.
- Recording a second survey for the same visit is rejected.
- Resolving a complaint without a resolution note is rejected.
- A Scheduled follow-up with `due_date` in the past becomes Missed after the daily job; running the job twice changes nothing more.
- A Receptionist cannot access the Users page or close a complaint.
- Every follow-up/complaint status change appears in the activity log with the acting user.
- Dashboard "average satisfaction" equals the mean of ratings in the selected period.

---

## 13. Future Extension (Level 2): Mini Audit

Designed so it can be added without reworking the CRM:
- Audit templates with weighted criteria; audit sessions per auditor.
- Answers: Compliant (1), Partial (0.5), Non-compliant (0), N/A (excluded).
- Score = Σ(weight × value) / Σ(weights excluding N/A) × 100.
- Audit states: Draft → In progress → Submitted → Finalized (manager must differ from auditor; answers lock on finalize).
- Corrective actions: Open → In progress → Done → Verified, with Overdue flag from a daily job; one action opened per Non-compliant answer on finalize.
- Later: facilities, KPI module, exports, REST API (Sanctum), Docker, CI.

---

## 14. Assumptions and Open Questions

- States, roles, and rules above are proposals, not confirmed internal rules of any company.
- Patient feedback is collected by staff; no automated messaging in the MVP.
- Single clinic only.
- Open: should a visit allow multiple follow-ups? (MVP: at most one.)
- Open: should survey ratings be editable by Admin? (MVP: delete only.)

---

## 15. Reconciliation with the shipped application

Reconciled on 2026-11-08 against the code in `app/`, `database/`, `resources/views/` and
`routes/`, and against the schema of the development database. "Shipped" means present in this
repository; nothing in this section has been implemented, and every item marked **missing** is
reported only.

### 15.1 Headline differences

| Topic | This document | Shipped application |
| --- | --- | --- |
| Framework | Laravel 11 | Laravel 13.34 on PHP 8.5 (`composer.json`) |
| Admin UI | Filament resources and pages | Hand-built Blade + Tailwind CSS v4, Arabic RTL, no admin-panel package |
| Audit trail | `spatie/laravel-activitylog` | Not installed. Attribution comes from `created_by` / `updated_by` / `deleted_by` columns on the domain tables. |
| Static analysis | Larastan | Not installed. Pint is used for style. |
| Visit | Recorded directly as completed; "no workflow" (§5.3) | A real state machine: `created → completed` or `created → cancelled`, editable only while `created` |
| Organisation | Not modelled | `clinics` and `doctors` tables, with statuses, schedules and a doctor→clinic FK |
| Patient identifier | Not modelled | `patients.mrn`, unique, generated on registration |
| Follow-up due scanning | Daily job `followups:mark-missed` | `followups:update-overdue`, scheduled **every minute** with `withoutOverlapping()` in `routes/console.php` |
| Status enums | `FollowUpStatus`, `ComplaintStatus`, `ComplaintSource`, `Gender` enums owning their transitions | Status values are plain strings held on the models/services. Enums exist for `AccountStatus`, `BloodGroup`, `Gender`, `Permission` and `Role`. |
| Events/listeners | Optional `SurveyRecorded`, `ComplaintStatusChanged` | Not present (`app/Events`, `app/Listeners`, `app/Jobs` do not exist) |

### 15.2 Actors (§2)

Implemented as specified: two roles (`RoleEnum`) and 38 permissions (`PermissionEnum`). Admin
holds every permission and is the only role admitted to `/dashboard`; receptionists get the
day-to-day patient, visit, follow-up, survey and complaint permissions but cannot manage users,
clinics, doctors, or assign/advance/reopen complaints. There is still no patient-facing portal.

### 15.3 MVP scope (§3)

| # | Item | Status |
| --- | --- | --- |
| 1 | Authentication and roles | Implemented (username + password, two roles, 38 permissions) |
| 2 | Patients | Implemented, and extended (MRN, blood group, allergies, chronic conditions, emergency contact, archive/restore) |
| 3 | Visits | Implemented, and extended into a state machine linked to a doctor and clinic |
| 4 | Follow-ups | Implemented |
| 5 | Satisfaction surveys | Implemented (one per completed visit) |
| 6 | Complaints | Implemented (auto from survey or manual) |
| 7 | Dashboard with KPIs | Implemented (Admin only, six KPI cards plus workflow tables) |
| 8 | Activity log for all important state changes | **Not implemented** — see §15.11 |

New scope that this document did not contain: **clinics**, **doctors**, **visit statuses**,
**MRN allocation**, **archive/restore for patients and doctors**, and **error pages** for
403/404/419/429/500.

### 15.4 Core workflow (§4)

The end-to-end journey matches: register patient → record visit → optionally schedule a follow-up
→ record a survey (completed visits only) → a rating below 3 opens a complaint automatically →
complaint moves through its stages.

Two details differ:

- step 2 says the receptionist records a "doctor name"; the application stores a `doctor_id`
  FK to a doctor record that belongs to a clinic, so a free-text doctor name is not possible.
- The visit is created with status `created` and must then be **completed** (optionally
  scheduling the follow-up in the same transaction) or **cancelled**. A completed visit freezes
  its date, doctor, reason and patient, and cannot be deleted.

### 15.5 State machines (§5)

**Follow-up.** `app/Services/FollowUpService.php` holds the transition table:

| This document | Shipped | Note |
| --- | --- | --- |
| Scheduled → Done | Implemented | Outcome note is **optional** (`nullable` in `CompleteFollowUpRequest`), not required as §5.1 states |
| Scheduled → Cancelled | Implemented | No reason note required |
| Scheduled → Missed | Implemented | Set by the scheduled command, **and** available as a manual transition |
| Missed → Done | **Missing** | Not allowed; a missed follow-up must first return to `scheduled` |
| Missed → Cancelled | Implemented | Added beyond this document |
| Cancelled → Scheduled | Implemented | Added beyond this document; §5.1 called Cancelled final |
| Done terminal | Implemented | Refused by both the update and the status action |

**Complaint.** Matches §5.2. `new → assigned` requires an assignee; `assigned → investigating →
resolved` requires a resolution note; `resolved → closed`. Reopening from `resolved` or `closed`
returns the complaint to `investigating` and clears `resolved_at` / `closed_at`, but it **does
not accept a reason**, which §5.2 asked for. Closing and reopening are administrator
permissions. A single `advance` endpoint moves the complaint one step along the happy path.

**Visit and survey (§5.3).** Contrary to "visit has no workflow", the visit has one. The survey
part is faithful: no edit route exists (`routes/surveys.php` exposes index, show, store and
destroy only), so a survey cannot be changed after creation; only deletion is available, and
deletion is refused when a complaint is attached.

### 15.6 Business rules (§6)

| Rule | Status | Where |
| --- | --- | --- |
| BR-1 patient phone unique | Implemented, **including archived patients** | `patients.phone` unique index |
| BR-2 visit belongs to an existing patient | Implemented | FK + `StoreVisitRequest` |
| BR-3 follow-up due date not before the visit | Implemented | `UpdateFollowUpRequest::rescheduleRule()` |
| BR-4 one survey per visit | Implemented | `surveys.visit_id` unique + service guard |
| BR-5 rating 1–5 | Implemented | Form request + column |
| BR-6 rating below 3 opens exactly one complaint | Implemented in one transaction | `SurveyService::submit()`; `complaints.survey_id` is unique |
| BR-7 no resolve/close without a resolution note | Implemented | `ComplaintService::advance()` inside the row lock |
| BR-8 missed only through the daily job | **Not honoured** | `scheduled → missed` is an allowed manual transition |
| BR-9 every transition written to an activity log | **Not implemented** | No activity-log package or table |
| BR-10 receptionists cannot manage users or close complaints | Implemented | Permission sets + policies |
| BR-11 patients soft-deleted, never hard-deleted | Implemented | `SoftDeletes` + `deleted_by`, restore action |
| BR-12 survey and auto-complaint in one transaction | Implemented | `SurveyService::submit()` |

### 15.7 Functional requirements (§7)

| Requirement | Status |
| --- | --- |
| FR-1 login/logout, roles, admin manages users | Implemented (users are soft-deleted; the last administrator is protected) |
| FR-2 patients CRUD, search, soft delete, profile with history | Implemented; search covers name, MRN and phone; archive/restore instead of a destructive delete |
| FR-3 visits with date, doctor, reason; filter by date and patient | Implemented, plus doctor and status filters and KPIs |
| FR-4 follow-ups, "today" and "missed" lists, daily job | Implemented as status filters and tiles; the scan runs every minute |
| FR-5 surveys with rating and comment | Implemented (completed visits only) |
| FR-6 complaints auto/manual, full workflow, filters | Implemented |
| FR-7 dashboard KPIs | Partially implemented: the dashboard shows counts for visits, follow-ups, satisfaction, complaints, clinics, doctors and patients plus the latest visits. There is **no** "new patients this month", no follow-up completion-rate metric and no average-time-to-resolve metric. |
| FR-8 activity log, admin-visible | **Not implemented** |

### 15.8 Data model (§8)

| Table | This document | Shipped schema |
| --- | --- | --- |
| `users` | `name, email, password, is_active` | `name, username, password, remember_token, deleted_at` — no `email`, no `is_active`; login is by username |
| `patients` | `full_name, phone, gender, birth_date, notes` | `fullname, phone, gender, birthdate, address, blood_group, allergies, chronic_diseases, emergency_name, emergency_phone, status, mrn, created_by, updated_by, deleted_at, deleted_by` — no `notes` |
| `visits` | `patient_id, visit_date, doctor_name, reason` | `patient_id, doctor_id, date, reason, visit_status, created_by, updated_by` |
| `follow_ups` | as specified | as specified plus `updated_by`; `due_date` is a datetime |
| `surveys` | `recorded_by` | `created_by, updated_by` |
| `complaints` | as specified | as specified plus `updated_by` |
| `clinics`, `doctors` | — | **Added**: clinic status/schedule fields, doctor specialty/schedule/status, soft deletes on doctors |

Relationship direction to remember when integrating: the survey↔complaint link is
`complaints.survey_id` (unique, nullable). `surveys` has no `complaint_id` column.

### 15.9 Screens (§9)

Filament resources are **not** used. The equivalent Blade screens exist for login, dashboard,
patients (list, create, edit, profile with history), visits (list, create, edit, detail),
follow-ups, surveys, complaints, clinics, doctors and users. The **activity-log screen does not
exist** because there is no activity log.

### 15.10 Technical design (§10)

- Services exist (`app/Services`) and hold the transactions, row locks and workflow checks;
  controllers stay HTTP-only, as intended.
- The *Action* classes named in §10.1 (`RecordSurveyAction`, `AssignComplaintAction`, …) are not
  separate classes; their logic lives in `SurveyService` and `ComplaintService`.
- Form Requests are used as specified (`app/Http/Requests`), and policies cover every domain
  model (`app/Policies`).
- The scheduled command is `followups:update-overdue`, not `followups:mark-missed`, and it runs
  every minute rather than daily.
- `spatie/laravel-activitylog`, `larastan/larastan` and `filament/filament` are **not** installed.
  Installed runtime packages are `laravel/framework`, `laravel/tinker`,
  `propaganistas/laravel-phone` and `spatie/laravel-permission`; development packages add Pest,
  Pint, Telescope, Pail and Boost.
- Factories and seeders exist for every model with synthetic Arabic data. Facility for testing is
  Pest with feature tests plus a separate two-process MySQL concurrency harness.
- Larastan does not run, so the §10.3 "Larastan at a reasonable level" quality bar is unmet; Pint
  and the test suite are met.

### 15.11 Acceptance criteria (§12)

| Criterion | Result |
| --- | --- |
| Rating 2 creates exactly one complaint (source `survey`); rating 4 creates none | Met |
| A second survey for the same visit is rejected | Met (unique `visit_id`) |
| Resolving a complaint without a resolution note is rejected | Met |
| An overdue scheduled follow-up becomes missed after the job, and running it twice changes nothing more | Met |
| A receptionist cannot reach the users page or close a complaint | Met (policy + permission tests) |
| Every follow-up/complaint status change appears in the activity log | **Failed** — no activity log |
| Dashboard "average satisfaction" equals the mean of the ratings in the period | Met for the displayed average; there is no period selector on the dashboard |

### 15.12 Roadmap (§11)

Steps 1–7 and 9 of the roadmap were completed with the deviations listed above. Step 8
(activity log) was **not** done. Step 10 (tests, seeders, README, screenshots) was done.

### 15.13 Future extension (§13)

Not implemented and not stubbed: there is no audit-template table, no audit session, no scoring,
no corrective action, no KPI module, no export, no API, no Docker file and no CI workflow. §13
remains a roadmap only.

### 15.14 Open questions (§14)

- Multiple follow-ups per visit: resolved to **at most one** by a unique index on
  `follow_ups.visit_id`.
- Editable survey ratings: resolved to **no edit, delete only**, guarded when a complaint exists.
  The application is also stricter than this document: only *completed* visits accept a survey.
