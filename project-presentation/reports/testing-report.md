# Testing Report

**Project:** Clinical Care Center — Medical Audit & Patient CRM
**Date:** 2026-11-08, extended 2026-11-09 (second and third passes — see section 10)
**Tester role:** Senior QA (read-only pass), extended by a presentation-and-regression pass and a
follow-up correctness pass
**Commit under test:** `ac48aef` (`main`) for the first pass; the uncommitted UI and follow-up
work described in section 10 for the second and third. Nothing from any pass has been committed.

> The database used for the browser pass is the local development database, populated by the
> project's own seeders with synthetic Arabic data. No real patient or staff record was read,
> changed or deleted. No credential used during testing appears in this report.

---

> **Correction (2026-11-09).** The first pass was strictly read-only. The second pass was not:
> producing the demo videos required walking the real patient journey end to end, which means
> records were created in the **local development** database with synthetic data. Section 5 and
> section 8 have been corrected accordingly, and section 10 records exactly what was written.

---

## 1. Environment

| Item | Value |
| --- | --- |
| Operating system | Windows 11 Pro (10.0.26200) |
| PHP | 8.5.12 (CLI and built-in server) |
| Laravel | 13.34.0 |
| Database (browser pass) | MySQL, database `Clinizone`, InnoDB |
| Database (test suite) | in-memory SQLite, configured in `phpunit.xml` |
| Application server | `php artisan serve` on `http://localhost:8000` |
| Browser | Chromium (Playwright 1.64.1), locale `ar-EG`, timezone `Africa/Cairo`, light colour scheme |
| Viewports | 1600×900 for the walkthrough and route sweep; 1440×900 at a device scale factor of 2 for the portfolio screenshots (2880×1800 PNGs); 390×844 at the same scale for the two mobile checks (780×1688 PNGs); 1440×810 for the two portfolio videos |

The application was started with the configuration already present in the repository. No
environment file, configuration value or dependency was changed for testing.

---

## 2. Test assets

| Asset | Contents |
| --- | --- |
| Pest suite | 23 feature test files plus one placeholder unit test (`tests/Unit/ExampleTest.php`) |
| Concurrency harness | `tests/Concurrency/` — 13 PHP files, two-process MySQL race checks, not part of `php artisan test` |
| Browser route sweep | Scripted GET of every application screen as an administrator, recording HTTP status, document title, `dir` and console output |
| Automated walkthrough | Scripted click-through of the full demo, recorded for the video and replayed without recording during development |
| Portfolio screenshots | 21 PNGs from the same seeded dataset |

---

## 3. Automated test suite

Command: `php artisan test --compact`

| Metric | Result |
| --- | --- |
| Tests | 171 |
| Assertions | 612 |
| Duration | 152.5 s (first pass) / 87.7 s (latest run, 2026-11-09) |
| Failures | 0 |

The suite passed on every test and every assertion. It was run again on 2026-11-09 after the
whole UI pass and again after the follow-up work that closed **O-02**, **O-05** and **F-07** —
171 tests, 612 assertions, 0 failures, 87.7 s — so the counts below are confirmed against the
modified views and the changed follow-up validation. The count rose from 166 to 171 because the
follow-up status rules gained the regression tests that O-02's new `missed` outcome and the
outcome-note requirement needed. The wall-clock difference between runs is machine load, not a
change in the suite. The README in the repository claimed 192 tests and 695 assertions as its
"latest verified run"; the measured figures above replace that claim in this pass's copy of the
README.

The MySQL concurrency harness (`tests/Concurrency/run_all.php`) was **not** re-executed in this
pass: it creates and drops a disposable `*_verify` database, which falls outside the read-only
constraint. Its last recorded result, from the repository's own README, is 12 scenarios and 64
checks, 0 failed, in roughly 35 seconds. That figure is reported as the project's claim, not as
something this pass verified.

---

## 4. Browser route sweep

Every screen below was opened as an administrator, after a real login, in the order listed. The
sweep was scripted from Chromium at 1600×900 and recorded HTTP status, document title, the `<html
dir>` value and console output for each request. The "Title rendered" column shows the
distinguishing part of the document title after the site-name prefix, which is
«مركز الرعاية السريرية المتخصصة - » on every page.

| # | Screen | Route | HTTP | Title rendered | `dir` |
| --- | --- | --- | --- | --- | --- |
| 1 | Dashboard | `/dashboard` | 200 | لوحة البيانات والتحليل السريري المتقدم (Clinical Dashboard) | rtl |
| 2 | Patient register | `/patients` | 200 | دليل وسجل الملفات السريرية (MRN Directory) | rtl |
| 3 | Patient creation form | `/patients/create` | 200 | فتح ملف مريض جديد (MRN Intake) | rtl |
| 4 | Patient file | `/patients/1` | 200 | الملف السريري الشامل - رشاد رزق كاظم | rtl |
| 5 | Patient search | `/patients?search=أنس` | 200 | دليل وسجل الملفات السريرية (MRN Directory) | rtl |
| 6 | Archived patients | `/patients?status=archived` | 200 | دليل وسجل الملفات السريرية (MRN Directory) | rtl |
| 7 | Visit register | `/visits` | 200 | سجل وزيارات المرضى والعيادات | rtl |
| 8 | Visit creation form | `/visits/create` | 200 | تسجيل زيارة سريرية جديدة | rtl |
| 9 | Visit detail | `/visits/1` | 200 | تفاصيل الزيارة السريرية #1 | rtl |
| 10 | Visit edit | `/visits/1/edit` | 200 | تعديل بيانات الزيارة السريرية | rtl |
| 11 | Completed visits | `/visits?visit_status=completed` | 200 | سجل وزيارات المرضى والعيادات | rtl |
| 12 | Cancelled visits | `/visits?visit_status=cancelled` | 200 | سجل وزيارات المرضى والعيادات | rtl |
| 13 | Follow-up register | `/follow_ups` | 200 | المتابعات السريرية والنتائج الحرجة (Follow-ups) | rtl |
| 14 | Follow-up detail | `/follow_ups/1` | 200 | تفاصيل المتابعة السريرية #1 | rtl |
| 15 | Surveys register | `/surveys` | 200 | استبيانات رضا المرضى (Surveys) | rtl |
| 16 | Surveys filtered by rating | `/surveys?rating=1` | 200 | استبيانات رضا المرضى (Surveys) | rtl |
| 17 | Complaints register | `/complaints` | 200 | إدارة الشكاوى والاعتراضات (Complaints) | rtl |
| 18 | Complaint detail | `/complaints/1` | 200 | تذكرة الشكوى #1 | rtl |
| 19 | Complaint edit | `/complaints/1/edit` | 200 | تعديل تذكرة الشكوى #1 | rtl |
| 20 | Clinics | `/clinics` | 200 | دليل العيادات والأقسام الطبية | rtl |
| 21 | Clinic creation form | `/clinics/create` | 200 | إضافة عيادة تخصصية جديدة | rtl |
| 22 | Clinic detail | `/clinics/1` | 200 | ملف العيادة التخصصية والأطباء التابعين | rtl |
| 23 | Clinic edit | `/clinics/1/edit` | 200 | تعديل بيانات العيادة التخصصية | rtl |
| 24 | Doctors | `/doctors` | 200 | دليل الأطباء والاستشاريين | rtl |
| 25 | Doctor creation form | `/doctors/create` | 200 | تسجيل طبيب واستشاري جديد | rtl |
| 26 | Doctor detail (archived record) | `/doctors/1` | 404 | 404 · الصفحة غير موجودة | rtl |
| 27 | Doctor edit (archived record) | `/doctors/1/edit` | 404 | 404 · الصفحة غير موجودة | rtl |
| 28 | Users | `/users` | 200 | إدارة المستخدمين والكادر الطبي | rtl |
| 29 | User creation form | `/users/create` | 200 | إضافة مستخدم جديد وتعيين الصلاحيات | rtl |
| 30 | User detail route | `/users/1` | 405 | — (framework response, no app layout) | — |
| 31 | User edit | `/users/1/edit` | 200 | تعديل بيانات الحساب والدور الوظيفي | rtl |

Nineteen screens rendered in Arabic RTL with no console output at all; the three non-200 rows
behave as the route table dictates (see below), and the filtered list views return the same title
as their unfiltered parent because the filter is applied in place.

### Non-200 results, explored

| Request | Observed | Explanation |
| --- | --- | --- |
| `/this-route-does-not-exist` | 404, branded Arabic error page, `dir="rtl"` | No matching route; the application's own error view renders, not a framework default |
| `/patients/999999` | 404, same branded error page, `dir="rtl"` | Route-model binding rejects the missing record |
| `/doctors/1` | 404, same branded error page | Records 1, 2 and 4 of `doctors` are **soft-deleted**; route-model binding excludes them. `/doctors/3` and `/doctors/3/edit` return **200** with titles «الملف المهني للطبيب وتفاصيل العيادة» and «تعديل بيانات الطبيب والاستشاري». Not a defect. |
| `/doctors/1/edit` | 404, same branded error page | Same cause as above |
| `/users/1` | 405, 35 KB framework response, no `dir` | The `users` resource deliberately omits `show` (`php artisan route:list --path=users` lists `index`, `create`, `store`, `update`, `destroy`, `edit` only), so `GET /users/{id}` has no handler. The users list links to edit directly. Not a defect. |

The `/users/1` request took 35 s to resolve in the sweep while every other request completed in
0.7–2.4 s. The response body itself is a static framework error page, so the delay points at the
single-threaded `php artisan serve` process rather than at application code: repeating the same
request in a fresh session returned the same 405 in 4.4 s and then 1.4 s. It is recorded here as
an observation about the development server, not as an application finding.

---

## 5. Workflows exercised in the browser

### 5.1 First pass (2026-11-08) — read-only

All of the following were performed against the seeded dataset. They are reads, filters,
navigation and **modal previews**; no record was created, updated, completed, assigned, closed,
archived, restored or deleted. Where a workflow reaches a state change, the last step before
submission was exercised (the modal opened, the field filled, the template chip applied) and then
cancelled. The second pass (section 10) repeated these and then went further; this subsection
describes the first pass only.

| # | Workflow | Steps performed | Result |
| --- | --- | --- | --- |
| 1 | Authentication | Logout state, wrong-field validation surfaces on the same screen; successful login redirects an administrator to `/dashboard` | Pass |
| 2 | Landing redirect | `/` → `/login` as a guest; `/` → `/dashboard` as an administrator | Pass |
| 3 | Dashboard | Six KPI cards render with live counts; workflow tables for complaints, urgent follow-ups and recent visits scroll into view; a KPI card responds to hover | Pass |
| 4 | Patient register | List, pagination, search by name (`أنس`), reset link | Pass |
| 5 | Patient file | Demographics, allergy/emergency block, chronic conditions, visit history, visit counter | Pass |
| 6 | Patient creation form | All sections, field-level layout, MRN hint, validation attributes | Pass (not submitted) |
| 7 | Visit register | Status tabs `all / created / completed / cancelled` each reload and update the KPI row; free-text search (`لينة`) | Pass |
| 8 | Visit detail | Doctor and clinic block, reason, status, audit footer, action availability | Pass |
| 9 | Survey modal (BR-6) | Open from a completed visit, choose a 2-star rating, confirm the warning banner appears; cancel | Pass |
| 10 | Follow-up register | List, status tiles, due-date emphasis | Pass |
| 11 | Follow-up completion modal | Open with the ✓ action, apply a quick template, observe the character counter; cancel | Pass |
| 12 | Surveys register | Rating distribution, filter by rating `1` | Pass |
| 13 | Complaints register | Status tiles, list, links to the originating survey | Pass |
| 14 | Complaint detail (new) | Assignee selector present; stage action available | Pass |
| 15 | Complaint detail (investigating) | Resolution note field and advance action | Pass |
| 16 | Clinics | Cards/rows with operational status and capacity | Pass |
| 17 | Doctors | Specialty, clinic, schedule, status | Pass |
| 18 | Users | Accounts list, roles (administrator only) | Pass |
| 19 | Responsive layout | Dashboard and off-canvas sidebar at 390×844 | Pass |

### Authorization checks

Role enforcement was exercised through the test suite and through route middleware rather than by
switching roles in the browser (the seeded receptionist accounts share a factory password that is
not part of the demo credentials):

- `/dashboard` is guarded by `role:Admin`;
- every domain controller calls `Gate::authorize` before it acts;
- the route sweep was performed as an administrator, which is the widest permission set.

---

## 6. Console and page errors

| Observation | Count | Where |
| --- | --- | --- |
| Console errors during the walkthrough | 0 | All screens |
| Page errors during the walkthrough | 0 | All screens |
| Expected resource-load messages during the route sweep | 3 | `/doctors/1`, `/doctors/1/edit` (404) and `/users/1` (405) — the browser reporting the deliberately non-200 requests above |
| Any other console or page error during the route sweep | 0 | The remaining 28 requests |
| `ReferenceError: openCompleteFollowupModal is not defined` | 1 | Reproduced by a scripted click on the broken button during the route sweep; described in `findings-and-recommendations.md` (F-01) |

The walkthrough avoids the broken button, which is why its own count is zero: the defect is
documented rather than worked around silently.

---

## 7. Data volume at the time of testing

Read from the development database (synthetic seed data):

| Table | Rows |
| --- | --- |
| patients | 101 |
| clinics | 10 |
| doctors | 30 |
| visits | 201 |
| follow_ups | 99 |
| surveys | 61 |
| complaints | 25 |
| users | 7 |

These volumes are enough to exercise pagination (15–20 rows per page), filtering, aggregate tiles
and the dashboard without loading unrealistic amounts of data.

---

## 8. Known limitations of this pass

- **No cross-browser testing.** Only Chromium was exercised. The emoji-based table icons in F-03
  are the most likely place for a rendering difference in Safari or on Android.
- **No receptionist-side browser session.** Role restrictions are covered by the Pest suite and by
  route middleware, not by a manual receptionist walkthrough.
- **No destructive or state-changing action was submitted** in the first pass, by design. The
  create, edit, complete, assign, resolve, close, archive and restore paths were exercised up to
  the point of submission and then cancelled; their committed behaviour is covered by the Pest
  suite. **This no longer describes the second pass** — see section 10.
- **The concurrency harness was not re-run** (see section 3).
- **No load, soak or penetration testing.** The login throttle and CSRF protection were verified
  by reading the code and by the existing tests only.

---

## 9. Verdict

The application is usable end to end with the seeded dataset: every screen renders in Arabic
RTL, every filter and search works, the workflow modals open with the correct pre-filled data,
and the browser console is clean. One visible defect (F-01) and two consistency issues (F-02,
F-03) are reported in [`findings-and-recommendations.md`](findings-and-recommendations.md); none
of them blocks a workflow, because each has a working alternative path in the same screen.

---

## 10. Second pass (2026-11-09) — UI, translation and end-to-end journey

### 10.1 What was under test

A presentation-only pass over the Blade layer: 37 tracked files changed (36 views and one feature
test), 366 insertions and 332 deletions, plus two new files — `lang/ar/roles.php` and
`resources/views/components/icon.blade.php`. No controller, service, action, model, form request, policy, gate, middleware,
route, migration, seeder or configuration file was touched, and no `@can` guard was removed or
relaxed. The findings behind these changes are in section 16 of
[`mimi-crm-analysis.md`](../../mimi-crm-analysis.md).

### 10.2 Regression run

| Metric | Result |
| --- | --- |
| Command | `php artisan test --compact` |
| Tests | 171 |
| Assertions | 612 |
| Failures | 0 |
| Duration | 87.7 s |

Every test in the first pass still passes, so nothing in the suite regressed. The count is five
higher than the first pass because the follow-up work that closed O-02 added regression tests for
the new `missed` outcome and the outcome-note requirement; no existing test was removed. Pint was
run over the changed files afterwards (`vendor/bin/pint --dirty`).

### 10.3 End-to-end journey, submitted for real

This is the part the first pass deliberately did not do. To produce the demo videos, one fictional
patient was taken through the complete lifecycle **through the real UI, with every form submitted**:

| # | Step | Submitted |
| --- | --- | --- |
| 1 | Sign in as an administrator | Yes |
| 2 | Dashboard overview and scroll | — |
| 3 | Register a new patient (name, birth date, gender, phone, blood group, allergies, chronic conditions, emergency contact) | Yes |
| 4 | Open the patient file, edit the address, save | Yes |
| 5 | Create a visit for that patient (doctor, reason) | Yes |
| 6 | Open the visit detail page | — |
| 7 | Complete the visit and schedule its follow-up in the same dialog | Yes |
| 8 | Answer the survey the visit register prompts for | Yes |
| 9 | Open the follow-up from its register and complete it with an outcome note | Yes |
| 10 | Raise a complaint, assign it, advance it to investigation | Yes |
| 11 | Walk the clinics, doctors and users screens | — |
| 12 | Return to the dashboard | — |

Both recordings ran this path; the driver reported `errors: []` (no page errors, no console
errors) for each.

### 10.4 What was written to the database

The writes went to the **local development MySQL database** only, and every value is synthetic —
the same class of data the project's own seeders create. No production database was contacted and
no existing seeded record was edited or deleted. Measured afterwards for the two demo patients:

| Table | Rows attributable to the demo runs |
| --- | --- |
| patients | 2 (one per demo patient; repeated runs reuse the existing file rather than creating a second) |
| visits | 8 |
| follow_ups | 8 |
| surveys | 8 |
| complaints | 5 |

The counts exceed the two final recordings because the journey was rehearsed several times while
the pacing was tuned. All of it is disposable: the rows are ordinary records in the development
database and `php artisan migrate:fresh --seed` removes them along with everything else.

### 10.5 Defects found and fixed in this pass

| ID | Finding | Verified by |
| --- | --- | --- |
| F-05 | The per-row follow-up dialog emitted duplicate ids and its character counter was bound to the first row's textarea | Counter now advances in the dialog that is open; verified in the recorded run |
| F-06 | `clinics/show` wrapped a whole `<td>` in `@can`, shifting the row against its header; `colspan="7"` on a five-column table | Column count matches the header; unauthorized viewer sees the em-dash placeholder |
| F-07 | `/visits/create?patient_id={id}` returned HTTP 500 (`Attempt to read property "id" on true`) | The page now returns 200 and still pre-selects the patient |

F-08 was found, and was closed in the third pass together with O-02 to O-05; two suspicions (a
`count()` on a paginator, duplicate survey modal ids) were investigated and dismissed. All are
documented in [`findings-and-recommendations.md`](findings-and-recommendations.md).

### 10.6 Deliverable verification

| Artifact | Check | Result |
| --- | --- | --- |
| `project-presentation/screenshots/` | 21 PNGs, all present, distinct content | 19 at 2880×1800 (desktop), 2 at 780×1688 (mobile), no duplicated image |
| `videos/full-demo.mp4` | Duration, encoding | 4 min 05.68 s, H.264 High / yuv420p / 1440×810, 8.49 MB |
| `videos/linkedin-demo.mp4` | Duration, encoding | 1 min 13.96 s, same profile, 2.56 MB |
| Both videos | Frozen or repeated screens | Sampled at 1 fps: the longest run of pixel-identical consecutive seconds is **1** in both |
| Both videos | Blank or loading frames | The smallest sampled second is 226 KB (full) and 278 KB (linkedin); a blank or spinner frame would be a fraction of that |
| Both videos | Mouse cursor drawn in every sampled second | **full-demo:** 246 seconds sampled, **0** with no cursor present. **linkedin-demo:** 74 sampled, **0** with no cursor present |
| Both videos | Cursor position matches the recorded interaction log | **full-demo:** 238 of 246 samples within 12 px of the logged mouse path and all 246 within 30 px (farthest 28 px); 28 of the 33 logged clicks found at the click's exact coordinates. **linkedin-demo:** 65 of 74 within 12 px of the path, 69 within 30 px; all 31 logged positions and all 12 logged clicks located |
| Both videos | Click effect present and synchronised with the press | **full-demo:** 12 of the 28 located clicks show an unambiguous ripple — a rise of 150–700 pixels of the ripple's own colour followed by a return to the pre-press level, peaking a median 0.58 s after the pointer arrives, matching the driver's settle-then-press pause. The remainder sit on screens already saturated with the same teal, where the ripple cannot be separated from the background by colour; four of them were confirmed by eye instead (see 10.7) |

### 10.6.1 How the cursor and click effect were verified

The recordings are made by a browser engine, which paints the page but never the operating
system's pointer — so the pointer and the click ripple are drawn *into the page* by the driver, as
a DOM overlay, and are therefore part of the recorded pixels rather than an annotation added
afterwards. That distinction matters: the arrow's position comes from the same mouse events that
drive the clicks, with no interpolation or easing applied to the overlay, so it cannot drift out
of step with the interaction it represents. The arrow is white with a dark outline and a soft drop
shadow, and the ripple is a 30 px circle in the project's own teal that scales up and fades over
460 ms.

Verification was done on the **exported MP4**, not on the live run, and independently of the
driver: frames were extracted and searched for the arrow's own signature — an isolated near-white
blob of 30–400 px tightly wrapped in dark pixels, a shape the application's interface does not
produce at that size — and each finding was then compared against the driver's own log of every
mouse position and click.

The ripple proved harder to detect automatically than the arrow. Its colour blends to roughly
(38, 127, 119) on the light sidebar, which is measurable, but on screens that are already teal —
the dashboard header, primary buttons, the active sidebar row — the ripple adds a few hundred
pixels to a field of several thousand and no colour threshold separates the two. The 12-of-28
figure above is therefore a floor, not a measurement of how many clicks are visibly rippling; the
ripple is drawn on every press by the same code path, and the 16 that the detector could not
resolve are masked by background, not missing.

### 10.7 Known limitations of this pass

- **No cross-browser testing.** Chromium only, as in the first pass.
- **No receptionist session in the browser.** Authorization is still covered by the suite and by
  route middleware; the pass changed only how permission-gated controls are *presented*, and every
  guard was left in place.
- **The concurrency harness was not re-run** (see section 3).
- **The journey was driven by a scripted browser**, not by hand. That is what makes it repeatable,
  but it also means only the paths in section 10.3 were exercised end to end.
- **The ripple could not be counted by colour on every click.** 16 of the 28 clicks located in
  `full-demo.mp4` sit on screens that already carry the ripple's own teal, so the automatic
  detector could not separate the two (section 10.6.1). The click effect is nevertheless legible
  where it is drawn: six consecutive frames at 25 fps from four of those clicks — at 92 s, 187 s,
  206 s and 227 s — were enlarged 2× and laid side by side, and the ring is visibly opening and
  fading around the press point in each while the arrow stays put. The limitation is of the
  detector, not of the export.

---

## 11. Third pass (2026-11-09) — follow-up correctness

The second pass was presentation-only. The third was not: it closed the five items the second pass
had left open, four of which reach backend code. It was carried out with the owner's explicit
approval, item by item, and it is the reason this report's test counts differ from the first
pass's.

### 11.1 What changed

| Item | File | Change |
| --- | --- | --- |
| O-02 | `app/Services/FollowUpService.php` | `TRANSITIONS['scheduled']` is `['done', 'cancelled']` — the hand-made jump to `missed` is gone |
| O-02 | `app/Http/Requests/FollowUpsRequests/CompleteFollowUpRequest.php` | `outcome_note` is `required\|string\|max:500` with an Arabic message |
| O-02 | `app/Http/Requests/FollowUpsRequests/UpdateFollowUpRequest.php` | `outcome_note` gains `required_if:status,done` with an Arabic message |
| O-02 | `app/Http/Requests/FollowUpsRequests/UpdateFollowUpStatusRequest.php` | `status` narrows from `in:scheduled,done,missed,cancelled` to `in:scheduled,cancelled` |
| O-03 | `app/Models/Complaint.php` | The five status labels lose their parenthesised English gloss |
| O-04 | `app/Models/User.php` | New `roleLabels()` accessor resolving each stored role through `lang/ar/roles.php` |
| O-05 | Views and components | Requirement ids moved out of visible text into HTML comments |
| F-07 | `app/Http/Controllers/VisitController.php` | `/visits/create?patient_id=` wraps the preselected patient in a list; an unknown id yields an empty list |
| F-08 | Follow-up views | The completion dialog's counter reads against the 500 the field and the rule both enforce |

The single behavioural narrowing a user could notice is that **`done` can no longer be reached
from the status endpoint.** That endpoint carries a status and nothing else, so it cannot satisfy a
rule that demands a note alongside the status; completion therefore goes through the completion
endpoint or the edit form, both of which require the note. Cancelling and rescheduling are
unchanged. `missed` is now set only by the scheduled `followups:update-overdue` command, and a
record that is already `missed` can still be edited and rescheduled.

### 11.2 Authorization

No policy, gate, middleware, route or `@can` guard was added, removed or relaxed in this pass.
Every changed endpoint keeps the guard it had:

| Endpoint | Guard |
| --- | --- |
| `PATCH follow_ups.status` | unchanged — the controller still authorizes before the service is called |
| `PATCH follow_ups.complete` | unchanged |
| `PUT follow_ups.update` | unchanged |
| `GET visits.create` | `Gate::authorize('create', Visit::class)` — still the first statement, before the fixed query |
| Complaint status labels | accessor only; no visibility rule touches it |
| `User::roleLabels()` | presentation accessor; the users screen still wraps its controls in the same `@can` guards |

The O-02 changes tighten what an *authorized* user may do. They do not widen what an unauthorized
one may reach.

### 11.3 Verification

| Check | Result |
| --- | --- |
| Full suite | 171 tests, 612 assertions, 0 failures, 87.7 s |
| New tests for the tightened rules | 5 added — no note refused on completion; no note refused on the edit form; hand-made `missed` refused; the overdue command still marks missed; the status endpoint refuses `done` |
| Existing tests | 3 re-pointed to the form that can now enforce the rule, none deleted; every other test unchanged and passing |
| `visits/create?patient_id={id}` | Returns **200** and still pre-selects the patient (was 500) |
| An unknown `patient_id` | Renders the form with an empty patient list rather than erroring |
| Complaint labels | Five labels render without the English gloss in both the register and the detail page |
| Role labels | Each role renders in Arabic wherever it appeared in Latin, with the stored name as fallback |
| Requirement ids | No longer in the rendered text; still present in the markup as HTML comments |
| Pint | `vendor/bin/pint --dirty` run over every changed PHP file |

### 11.4 What was not done

- **Nothing has been committed.** Every change from all three passes is still in the working tree.
- **Both videos and all 21 screenshots now come from the code as it stands.** File times settle
  this rather than memory. Both videos were exported at 14:27:35 and 14:28:11, after the last
  third-pass source change at 13:30. The screenshots were first captured at 12:29:11 to 12:35:15
  — before the first third-pass view change at 13:22:03 — and `git status --porcelain
  project-presentation/screenshots/` was empty, so up to this point they were the committed
  first-pass captures; six of them photographed screens that O-03, O-04, O-05 or F-08 had since
  changed. All 21 were therefore recaptured at 15:02:58 to 15:05:35 against the current code.
  The run registered no console or page errors and wrote 20 shots covering the 19 desktop frames
  (`03-patients` is photographed twice, before and after the search), and the mobile pass wrote
  the remaining 2. Every frame kept its original pixel size: 2880×1800 for the desktop set and
  780×1688 for the mobile pair.
- **What the recapture actually changed.** Thirteen of the 21 files differ from the bytes they
  replaced and eight are byte-identical. The identical eight are the ones whose views render the
  same as before: `01-login`, `04-patient-create`, `05-patient-profile`, `17-clinics`,
  `18-doctors` and `21-mobile-sidebar` were untouched by this pass, and `15-complaint-new` and
  `19-users` were touched only by the refactor that moved the role-name mapping out of each
  Blade template into `User::roleLabels()` — the same text, produced in one place instead of
  seven. `19-users` being byte-identical is the expected result and a useful check that O-04 was
  a refactor rather than a wording change. The substantive differences are `14-complaints` and
  `16-complaint-investigating` (O-03 glosses gone) and `10-followups` and
  `11-followup-completion` (F-08 counter). The remaining differences are the dashboard and
  register frames, including `20-mobile-dashboard`, which changed because the capture run itself
  added a visit, a follow-up, a survey and a complaint to the demo patient, moving the counts
  those pages print. The run reused the existing demo patient rather than registering a second
  one, so the patient directory did not grow and no duplicate file was left behind.
- Section 10.3's journey was recorded before O-02 narrowed the status rules, so in those
  recordings the follow-up is still completed from its own dialog — which remains the supported
  path — and no visible step in them became invalid. The recordings do not, however, demonstrate
  the new refusals.
- **The concurrency harness was still not re-run**, for the same reason as before.
