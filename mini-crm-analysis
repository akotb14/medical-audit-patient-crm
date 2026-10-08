# Mini Patient CRM (Clinic Care Tracker) — Analysis & Requirements

> Purpose: single source of truth for building the project. Written to be used as context for Claude Code.
> Stack: Laravel 11, MySQL or PostgreSQL, Filament (admin UI), Pest.

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
