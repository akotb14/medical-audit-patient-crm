# Findings and Recommendations

**Project:** Clinical Care Center — Medical Audit & Patient CRM
**Date:** 2026-11-08, extended 2026-11-09 (second and third resolution passes)
**Scope:** QA and documentation pass over the application as committed at `ac48aef`, followed by
three resolution passes on 2026-11-09: the first implemented F-01, F-02 and F-03; the second was a
UI, translation and Blade-markup audit across all 37 affected view files (see section 16 of
[`mimi-crm-analysis.md`](../../mimi-crm-analysis.md)) and implemented F-05, F-06 and F-07; the
third closed every item the second pass had left as "needs a backend change" — F-07 in the
controller, F-08, and O-02 to O-05.
**Method:** Static review of the Blade views, controllers, form requests, services, models,
routes and schema; a scripted browser walkthrough of every screen as an administrator; a full
end-to-end patient journey driven through the real UI and recorded; and the
existing Pest suite. The findings were recorded first, read-only; the resolutions were then
applied to the Blade views and re-verified in the browser and against the affected feature tests.

> All observations come from the seeded synthetic dataset. No real patient, staff or
> credential data appears in this report.

---

## Summary

| ID | Severity | Area | Finding | Status |
| --- | --- | --- | --- | --- |
| F-01 | **High** | Follow-ups UI | An action button in the follow-up register calls a JavaScript function that does not exist; clicking it throws a `ReferenceError` and does nothing | **Resolved** — direct modal call plus `@can` guard |
| F-02 | Medium | Responsive layout | At a 1280 px-wide desktop viewport the follow-up register's action column starts off-screen; its horizontal scroll has no visible affordance | **Resolved** — sticky action column with edge shadow |
| F-03 | Low | Visual consistency | Table action icons are emoji glyphs in 13 views while navigation uses hand-drawn SVG | **Resolved** — shared inline-SVG set |
| F-04 | Low | Documentation | README carried stale test counts and a link to a deleted document | **Resolved** in the documentation pass |
| F-05 | Medium | Markup / JavaScript | The per-row follow-up dialog emitted duplicate element ids, and its character counter was bound to the *first* row's textarea, so it never updated in any other row's dialog | **Resolved** — per-row ids and a delegated listener |
| F-06 | Low | Markup | `clinics/show` wrapped a whole `<td>` in `@can`, shifting the row one column against its header; the empty state used `colspan="7"` on a five-column table | **Resolved** |
| F-07 | **High** | Routing / view contract | `/visits/create?patient_id={id}` returned HTTP 500: the controller hands the view either a single `?Patient` or a collection, and the view iterated the model | **Resolved** — the controller always passes a collection, and the view keeps its guard |
| F-08 | Low | Markup | `followups/index` renders its per-row modals inside `</tbody>` | **Resolved** — modals moved below `</table>` |
| O-01 | Info | Data model | The survey → complaint link is `complaints.survey_id`; `surveys` has no `complaint_id` | No action — documented |
| O-02 | Info | Spec fidelity | Two documented business rules are relaxed in the shipped code (see Recommended next steps) | **Resolved** — both rules tightened |
| O-03 | Info | Localization | `Complaint::getStatusLabelAttribute()` / `getSourceLabelAttribute()` embed the English term in brackets, so a badge reads `جديدة (New)` while the filter beside it now reads `جديدة` | **Resolved** — English gloss dropped |
| O-04 | Info | Blade architecture | Localizing a role name is written out in seven views because `User::roleNames()` returns a display string from inside the model | **Resolved** — `User::roleLabels()` accessor |
| O-05 | Info | Source hygiene | Requirement ids (`FR-5`, `BR-7`, …) are served to the browser in HTML comments across the complaints, dashboard and survey views | **Resolved** — comments removed |

Every item raised in the three passes is closed except **O-01**, which needs no action.

Nothing else was found that blocks use of the application. Every screen loads, every tested
workflow behaves as the README describes, and the console is clean. Two suspicions raised during
the audit were investigated and **dismissed**; they are recorded at the end of this report so they
are not re-opened as bugs.

---

## F-01 — Dead "record outcome" button in the follow-up register

**Severity:** High (visible, clickable, does nothing)
**Where:** [`resources/views/followups/index.blade.php:283-287`](../../resources/views/followups/index.blade.php)

```blade
<button type="button"
    onclick="openCompleteFollowupModal({{ $flw->id }}, ...)"
    class="text-[11px] text-[#00685f] hover:underline font-bold flex items-center gap-1">
    <span>+ توثيق النتيجة</span>
</button>
```

`openCompleteFollowupModal` is called here and **defined nowhere** in the project:

```text
$ grep -rn "function openCompleteFollowupModal" resources app public
(no results)
```

A cross-check of every inline `onclick` handler in `resources/views` against every function
definition in the views confirms this is the only unresolved handler:

| Handler called | Defined? |
| --- | --- |
| `openCreateSurveyModal`, `closeCreateSurveyModal` | Yes — `components/create-survey-modal.blade.php` |
| `setCompleteVisitDueDate` | Yes — `components/complete-visit-with-followup-modal.blade.php` |
| `openAssignModal`, `closeAssignModal`, `applyOutcomeTemplate` | Yes — `complaints/index.blade.php`, `followups/index.blade.php` |
| **`openCompleteFollowupModal`** | **No** |

**Reproduction**

1. Sign in as an administrator.
2. Open `/follow_ups`.
3. Click **«+ توثيق النتيجة»** on any row whose status is `scheduled`.
4. Nothing opens; the browser console logs `ReferenceError: openCompleteFollowupModal is not
   defined`.

**Impact.** The completion modal remains reachable through the check-mark button in the same
row's Actions column (`title="إتمام وتوثيق المتابعة (Complete)"`, line 303), which calls
`document.getElementById('completeFollowupModal-{{ $flw->id }}').classList.remove('hidden')`
directly. The workflow is therefore not blocked, but the more prominent, better-labelled entry
point is broken — and it is the one placed in the "Outcome note" column, where a receptionist
would look first.

**Note.** Unlike the check-mark button, this button carries **no** `@can('complete', $flw)`
guard, so it is rendered for every user regardless of permission.

**Recommendation.** Replace the `onclick` with the same direct call the Actions column already
uses, or define one shared helper next to the modal component and have both buttons call it. Add
the `@can('complete', $flw)` guard while doing so.

**Resolution (2026-11-09).** The button now uses the same direct call as the Actions column and
carries the same `@can('complete', $flw)` guard, with an `@else` dash for users who may not
complete the follow-up. The change also removed the `addslashes($flw->patient->fullname)`
argument, which dereferenced `patient` for follow-ups that have none. Verified in Chromium at
1280×900: 15 outcome buttons render, clicking the first opens `completeFollowupModal-57`, and the
page raises **0** errors (previously `ReferenceError: openCompleteFollowupModal is not defined`).

---

## F-02 — Follow-up actions are off-screen at 1280 px

**Severity:** Medium (usability, not data loss)
**Where:** `resources/views/followups/index.blade.php` (table inside `div.overflow-x-auto`, line 148)

Measured in Chromium with the follow-up register open at 1280×900:

```text
✓ complete button bounding box: { x: -224, y: 530.8, width: 23, height: 30 }
```

The table is wider than the viewport. The wrapper does scroll (`overflow-x-auto`), so nothing is
lost, but in this RTL layout the Actions column — the column that actually moves a follow-up
forward — sits entirely to the **left** of the visible area on first paint, and there is no
shading, gradient or scrollbar hint to say so. A user on a 1280-wide laptop sees the outcome
column but no way to act on it.

The same table at 1600×900 fits without scrolling, which is why the demo videos were recorded at
1600×900.

**Recommendation.** Either (a) collapse secondary columns (creator, completion timestamp,
outcome note) into an expandable row or a detail view below `xl`, or (b) make the horizontal
scroll obvious with a sticky action column plus an edge shadow. Option (a) also benefits the
visits and complaints registers, which share the same shape.

**Resolution (2026-11-09).** Option (b) was implemented in the follow-up, visit and complaint
registers: the Actions `<th>` and `<td>` are `sticky left-0` inside their `overflow-x-auto`
wrapper, the cell keeps an opaque background that follows the row's hover/overdue state so
content does not show through, and a soft edge shadow marks the boundary. Measured in Chromium
at 1280×900, the action cell sits at `x = 33` for all 15 follow-up rows and stays at `x = 33`
after scrolling the table to its far end, which confirms it is pinned; the complaints register
behaves the same (`x = 33`, right edge 153). The visits register does not overflow at 1280
(`tableScrolls: false`). At 1600×900 the same assertions hold (15 rows, 60 action icons,
`minX = 33`).

---

## F-03 — Emoji icons in tables vs SVG icons in navigation

**Severity:** Low (polish and accessibility)
**Where:** 13 views, e.g. `resources/views/components/visits/table.blade.php:104,121,129,138`

The sidebar and header use inline SVG (11 and 3 `<svg>` elements). Every table's action buttons
instead use emoji characters — `👁️` (view), `✏️` (edit), `🗑️` (delete), `✓` (complete) — in:

```text
clinics/index, clinics/show, complaints/edit, complaints/index, complaints/show,
components/visits/table, dashboard/index, doctors/show, followups/edit, followups/index,
patients/index, surveys/index, visits/edit
```

Consequences: the glyphs render differently on Windows, macOS and Android; they ignore the
`text-*` colour of their button, so hover and disabled states are invisible on them; and screen
readers announce the emoji name ("eye", "wastebasket") instead of the `title` text.

**Recommendation.** Adopt one icon system for action buttons — the same inline-SVG approach the
sidebar already uses — and keep the Arabic `title` attributes as the accessible name.

**Resolution (2026-11-09).** A shared `<x-icon>` component
([`resources/views/components/icon.blade.php`](../../resources/views/components/icon.blade.php))
now draws `eye`, `pencil`, `trash` and `check` with the same stroke weight and line caps as the
sidebar. Every table action glyph in the 13 views was replaced with it; the Arabic `title`
attributes remain as the accessible name and the decorative SVG is `aria-hidden="true"`.
Verified: no `👁️`/`✏️`/`🗑️` remain under `resources/views`, and the registers render four SVGs
per action cell (60 across 15 follow-up rows, 80 across 20 visits, 45 across 15 complaints).
Emoji kept inside `<select>` status labels were left alone — they are option text, not action
icons.

---

## F-04 — Documentation drift (resolved)

**Severity:** Low
**Where:** `README.md`

Two inaccuracies were present and have been corrected in the README that ships with this
presentation:

- the "latest verified run" figure did not match the current suite; the counts in the README now
  match the run recorded in `testing-report.md`;
- a "Historical analysis documents" link pointed at `analysis-and-requirements.md`, which was
  deleted in commit `ac48aef`. The section now describes the document and its removal without
  linking to it.

---

## F-05 — Duplicate ids and a dead character counter in the follow-up dialog

**Severity:** Medium (a visible control silently does nothing)
**Where:** `resources/views/components/complete-followup-modal.blade.php`

The component renders once per follow-up row, and it used to hard-code every id it contained:
`completeFollowupForm`, `complete_outcome_note`, `outcomeCharCount`, `completeModalFollowupId`,
`completeModalPatientName`. The register therefore emitted the same id once per row — invalid
HTML — and the script that kept the counter up to date resolved the textarea by document-wide id:

```js
document.getElementById('complete_outcome_note').addEventListener('input', function () {
    document.getElementById('outcomeCharCount').textContent = `${this.value.length} / 2000 حرف`;
});
```

`getElementById` always returns the **first** match, which is the first row's dialog. Opening any
other row's dialog and typing left the counter in the *open* dialog frozen at `0 / 2000 حرف`,
while the hidden first dialog's counter advanced. The same duplicated id made every row's
`<label for="complete_outcome_note">` point at the first row's textarea, so clicking the label did
not focus the field the user was looking at. The quick-template helper `applyOutcomeTemplate` was
also redefined once per row.

**Resolution (2026-11-09).** The textarea id is derived from the record
(`complete_outcome_note_{{ $followup->id }}`), so the label now points at its own field; the
counter listens once on `document` and finds its display through
`textarea.closest('form')?.querySelector('[data-outcome-counter]')`; and the template helper is
declared once behind an `if (typeof window.applyOutcomeTemplate === 'undefined')` guard. Form
actions, field names, routes and HTTP methods are unchanged, and the Pest suite still passes
(171 tests, 612 assertions as of the final run; 166 at the time of this fix).

The counter's denominator was also wrong on its own: it read `0 / 2000 حرف` while the field's
`maxlength` and the validation both stop at 500, so the last three quarters of the advertised
range were unreachable. It now reads `0 / 500 حرف`, matching the field it counts. This was a
display-only correction — no route, rule or stored value changed.

---

## F-06 — A malformed row in the clinic detail table

**Severity:** Low (visual)
**Where:** [`resources/views/clinics/show.blade.php`](../../resources/views/clinics/show.blade.php)

The doctors table wrapped an entire `<td>` — not the control inside it — in `@can('view', $doctor)`:

```blade
@can('view', $doctor)
    <td class="py-3 px-3 text-left"> … </td>
@endcan
```

A viewer who may not open a doctor's file therefore lost a whole cell and the row shifted one
column against the header. The empty-state row also declared `colspan="7"` for a table with five
columns.

**Resolution (2026-11-09).** The directive now wraps only the inner link, with an `@else` em-dash,
so the cell always renders and an unauthorized viewer sees a neutral placeholder rather than a
misaligned row. Nothing new became visible: the link is still absent for that viewer. `colspan`
corrected to `5`.

---

## F-07 — HTTP 500 on `/visits/create?patient_id={id}`

**Severity:** High (the page did not load)
**Where:** `app/Http/Controllers/VisitController::create()` and `resources/views/visits/create.blade.php`

`create()` assigns `$patients` from two different sources:

```php
$patients = is_numeric($patientId)
    ? $this->patientService->getPatientOption((int) $patientId)   // returns ?Patient
    : $this->patientService->getPatientOptions();                 // returns a Collection
```

`PatientService::getPatientOption()` returns a single `?Patient` model. The view then iterated it
with `@foreach ($patients ?? [] as $patient)`, and an Eloquent model iterates its **public
properties**, so the loop's first item was the `exists` flag (`true`) and the template died with
`Attempt to read property "id" on true`. Any link that pre-selects a patient — including the
"create a visit" action reached from a patient's file — produced a 500.

**Resolution (2026-11-09).** The view normalizes what it is handed before iterating, so the page
loads whether it receives a model, a collection or `null`, and the preselected patient is still
selected.

**Resolution in the controller (2026-11-09, third pass).** The view guard stopped the 500 but left
the contract ambiguous, so the controller was corrected as originally recommended. The
`patient_id` branch now wraps its single option in a collection:

```php
$patients = collect([$this->patientService->getPatientOption((int) $patientId)])->filter();
```

`create()` therefore always hands the view a `Collection` — one element, or none when the id does
not match a patient — and the view is a plain loop again. The guard was kept as a second line of
defence, not as the fix.

---

## F-08 — Per-row modals inside `</tbody>`

**Severity:** Low (markup only; no symptom observed)
**Where:** `resources/views/followups/index.blade.php`

The follow-up register includes its `delete-followup-modal` and `complete-followup-modal`
partials between the last `<tr>` and `</tbody>`. Browsers hoist the stray `<div>`s out of the
table and the page renders correctly, so no user-visible defect was observed in any recording.

**Resolution (2026-11-09, third pass).** The partials now render after `</table>`: the register
closes `</tbody>` and `</table>` first, then includes `x-complete-followup-modal` and
`x-delete-followup-modal` for each row. The markup is valid again and the register renders exactly
as it did before, so the recording is unchanged.

---

## Suspicious behaviour that turned out not to be a defect

Both of these were raised while auditing, investigated against the running code, and **withdrawn**.
They are recorded so that a later reader does not spend time on them again.

1. **`$clinics->count()` on a paginator.** The clinic register prints `count()` on `$clinics`, and
   a count on a `LengthAwarePaginator` returns the page size rather than the total — a classic
   mix-up. It is not happening here: `ClinicController::index` passes
   `ClinicService::getClinicsWithDoctors()`, which returns a `Collection`, so `count()` is the
   total number of clinics.
2. **Duplicate `createSurveyModal` ids.** The survey dialog contains fixed ids, and the component
   is included from two screens, which would collide if both were ever rendered on one page. It
   is not: each screen includes it exactly once (`visits/index`, `dashboard/index`).

---

## O-01 — Survey/complaint relationship direction (informational)

`complaints.survey_id` is a nullable unique FK. There is **no** `surveys.complaint_id` column.
Any integration, report or query that walks from a survey to its complaint must go through
`Survey::complaint()` → `complaints.survey_id`, and any attempt to select `s.complaint_id` will
fail with `Unknown column`.

`SurveyService::submit()` creates the survey and the complaint in one transaction using this
column, which is also what makes BR-6 idempotent: the unique index on `survey_id` permits at most
one auto-created complaint per survey.

---

## O-02 — Business rules that are more relaxed than the specification

Neither is a defect in the application; both are differences from
[`mimi-crm-analysis.md`](../../mimi-crm-analysis.md) that a reviewer should know about. Both are
documented in section 15 of that file.

1. **Completing a follow-up without an outcome note.** The specification requires an outcome note
   when a follow-up moves to `done`. `CompleteFollowUpRequest` validates `outcome_note` as
   `nullable`, so a follow-up can be completed with an empty note.
2. **Marking a follow-up missed by hand.** The specification says only the scheduled command may
   set `missed`. The transition table in `FollowUpService` also allows `scheduled → missed`
   through the normal status action.

**Recommendation.** Decide whether these are intended. If not, make `outcome_note` required when
the target status is `done`, and remove `missed` from the manually selectable options while
leaving it available to the command.

**Status (2026-11-09, third pass): resolved — the code was brought up to the specification.**
The product owner asked for the specification to win, so both rules were tightened rather than the
document being amended:

1. `CompleteFollowUpRequest` now validates `'outcome_note' => 'required|string|max:500'` with an
   Arabic message (`يرجى تدوين نتيجة المتابعة قبل إتمامها.`), so the completion dialog cannot be
   submitted empty. `UpdateFollowUpRequest` applies the same rule on the edit form through
   `required_if` on the status: a follow-up may still be rescheduled or cancelled without a note,
   but it cannot be moved to `done` without one.
2. The transition table no longer allows the hand-made jump:
   `'scheduled' => ['done', 'cancelled']`. `missed` is reachable only from the scheduled
   `followups:update-overdue` command, and from `missed` the register can still reschedule or
   cancel (`'missed' => ['scheduled', 'cancelled']`). The edit form keeps a `missed` option, but
   only renders it when the follow-up is *already* missed, so an existing missed record is
   preserved on save instead of being silently moved.

Five tests were added: completing without a note is refused, moving a follow-up to `done` from the
edit form without a note is refused, the hand-made transition to `missed` is refused, the overdue
command can still make it, and the status endpoint refuses `done`. Three existing tests were
re-pointed rather than deleted, because the rule they assert is unchanged but the route that can
now enforce it is different: the status endpoint carries a status and nothing else, so it cannot
satisfy a rule that requires a note alongside the status. "Completing a follow-up records the
completion moment" is now driven through `follow_ups.update`, and "a cancelled follow-up cannot
reach `done`" is asserted against the same form. The behaviour those tests protect — a cancelled
follow-up never becomes done, a completed one records its moment — is still covered.

---

## O-03 — The English gloss is baked into the complaint label accessors

**Where:** `app/Models/Complaint.php:67` and `:91`

```php
public function getStatusLabelAttribute(): string
{
    return match ($this->status) {
        'new' => 'جديدة (New)',
        'assigned' => 'مسندة للتحقيق (Assigned)',
        // …
    };
}

public function getSourceLabelAttribute(): string
{
    return match ($this->source) {
        'survey' => 'استبيان رضا (تلقائي)',
        'manual' => 'تسجيل يدوي (مباشر)',
        // …
    };
}
```

The second resolution pass removed raw database tokens from the complaint screens' filter selects,
tiles and stepper, which now read `جديدة`, `مسندة`, `قيد التحقيق`, `تم الحل`, `مغلقة`. The badges
those same screens render still come from these accessors, so the register now shows `جديدة` in
the filter and `جديدة (New)` on the row. Neither string is wrong on its own; together they are
inconsistent.

**Status (2026-11-09, third pass): resolved.** The parenthesised English term was dropped from
both accessors, exactly as recommended. The status labels now read `جديدة`, `مسندة للتحقيق`,
`قيد التحقيق`, `تم الحل`, `مغلقة`, so a badge and the filter beside it use the same wording:

```php
'new' => 'جديدة',
'assigned' => 'مسندة للتحقيق',
```

The source labels kept their Arabic qualifier — `استبيان رضا (تلقائي)`, `تسجيل يدوي (مباشر)` —
because it is descriptive text in the interface language rather than a second name for the same
state, and it is the same string the register's source filter shows.

One difference remains, recorded rather than smoothed over: a badge carries the fuller form
(`تم الحل والمعالجة`, `قيد التدقيق والتحقيق`, `مغلقة نهائياً`) while the filter select and the
lifecycle stepper use the short form (`تم الحل`, `قيد التحقيق`, `مغلقة`). The two no longer
contradict each other and `new` matches exactly, so this is a wording preference, not the defect
this item reported — the defect was the English term, and that is gone.

---

## O-04 — Role localization is duplicated across seven views

**Where:** `users/index`, `users/create`, `users/edit`, `complaints/index` (twice),
`complaints/create`, `complaints/show`

Role names are stored in Latin (`Admin`, `Receptionist`) and are now translated through
`lang/ar/roles.php`. Because a view may not add a PHP helper class in this pass, the resolution —
split on `,`, trim, drop empties, translate with a fallback, rejoin with `،` — is written out at
every call site:

```blade
{{ collect(explode(',', $user->roleNames()))->map(fn ($role) => trim($role))->filter()
    ->map(fn ($role) => trans()->has('roles.' . $role) ? __('roles.' . $role) : $role)
    ->implode('، ') ?: 'بدون دور' }}
```

The root cause is upstream: `User::roleNames()` already returns a **display** string from inside
the model, so every screen that wants it translated has to redo the pipeline.

**Status (2026-11-09, third pass): resolved.** `User::roleLabels()` was added exactly as
recommended and all seven call sites now call it:

```php
public function roleLabels(string $fallback = 'بدون دور'): string
```

The method owns the whole pipeline — split on `,`, trim, drop empties, translate through
`lang/ar/roles.php`, join with `،` — and returns the fallback when the user has no role. A grep
for the old inline `collect(explode(',', $user->roleNames()))` expression across `resources/views`
now returns nothing, so the duplication is gone rather than merely reduced. `roleNames()` is
untouched and still returns the raw stored string, which is what the accessor builds on.

The presentation views are the only callers, so the new accessor does not change any stored data
or any authorization decision — it is a display string.

---

## O-05 — Requirement ids are served in HTML comments

**Where:** `complaints/create`, `complaints/index`, `complaints/show`,
`components/create-survey-modal`, `components/complete-followup-modal`, `dashboard/index` and
others — about twenty comments in all, of the form
`<!-- Assigned To (FR-6) -->`, `<!-- Resolution Note Card (BR-7) -->`,
`<!-- KPI 3: Satisfaction Index (FR-5, BR-5) -->`.

They are not rendered, so no user sees them, and they were deliberately left in place: deleting
them is comment churn across many files for no user-visible gain. They are recorded because
`view-source` on a deployed instance exposes internal requirement numbering.

**Status (2026-11-09, third pass): resolved.** All of them were removed. A grep for `FR-` and
`BR-` across `resources/views` now returns nothing, so `view-source` on a deployed instance no
longer exposes the internal requirement numbering. The traceability was not lost: it lives in
[`mimi-crm-analysis.md`](../../mimi-crm-analysis.md), which is the document that owns those ids.

---

## What was verified as working

Recorded here so the findings above are not mistaken for a general verdict:

- every application screen returns HTTP 200 for an administrator, in both list and filtered form;
- the login throttle, session regeneration and logout invalidation are covered by tests and
  behave as documented;
- the visit workflow (`created → completed` / `cancelled`), its frozen-history guard and its
  refusal to delete completed visits behave as documented;
- the follow-up completion modal writes the outcome note and completion timestamp and refuses a
  second completion;
- BR-6 fires exactly once: rating 2 shows the warning banner, rating 5 does not, and the survey
  and complaint are written together;
- the complaint advance flow enforces the assignee and the resolution note, and reopening clears
  the resolution timestamps;
- the patient archive/restore path keeps the record out of the active register without deleting
  it;
- search, status filters, pagination and the RTL layout behave consistently in Arabic.

### Verified again by driving the whole journey (2026-11-09)

The second pass ran one fictional patient through the complete path in the real UI, twice, for the
two recordings: register the file, edit it, open a visit, complete the visit and schedule its
follow-up in the same dialog, answer the survey the register then prompts for, complete the
follow-up from its register, raise a complaint and advance it through assignment to
investigation, then walk the clinics, doctors and users screens. Every step submitted for real and
every redirect landed on the expected screen; the scripted driver recorded **0** page errors and
**0** console errors across both runs. The character counter in the follow-up dialog — the control
F-05 was about — advanced as the outcome note was typed, in the dialog that was actually open.

---

## Recommended next steps, in order

1. **Consider adding the missing activity log** — this is the largest unimplemented item in the
   specification (BR-9, FR-8). It is a feature request, not a defect: the current audit columns
   name the last actor but do not keep a history of transitions.
2. **Decide the badge wording** — the last item this report leaves open is stylistic: status
   badges carry the fuller Arabic form while filters and the stepper use the short one (see O-03).
   Picking one form for both is a one-line change per label.
3. **Re-run the presentation captures if either changes** — the screenshots and both videos were
   taken from the code as it stands now, so a wording change would need them re-shot.

F-01, F-02 and F-03 were implemented and verified on 2026-11-09 as described in each section;
F-04 was corrected in the documentation pass; F-05, F-06 and F-07 were implemented and verified in
the second pass; F-08, O-02, O-03, O-04 and O-05 were implemented and verified in the third, and
O-01 needs no action. The affected feature suites (`FollowUpWorkflowTest`, `ComplaintWorkflowTest`,
`VisitWorkflowTest`, `SurveyWorkflowTest`) passed after the first set of changes — 71 tests, 254
assertions — and the **entire** suite passed after the third: 171 tests, 612 assertions, 0
failures, 87.7 s. Five tests were added in the third pass to cover O-02's new `missed` outcome and
the outcome-note requirement, and three were re-pointed at the form that now enforces the rule
they were already asserting (see O-02); none was deleted.

The only item left in this document is the badge-versus-filter wording below, which is recorded
as a recommendation rather than a defect and was deliberately not changed without a decision on
it.
