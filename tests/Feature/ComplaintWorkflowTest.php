<?php

use App\Models\Complaint;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-008, REVIEW-015 and REVIEW-NEW-011.
|
| REVIEW-008 — the update endpoint accepted `status` from the payload, so a
| complaint could jump from `new` to `closed` without passing through the
| workflow.
| REVIEW-015 — `advance`, `assign` and `reopen` changed the complaint without
| recording who did it, leaving `updated_by` untouched.
| REVIEW-NEW-011 — the patient link was guarded by `show`, an ability
| `PatientPolicy` never defines, so the link never rendered for anybody.
| The complaint screens also asked the gate about `App\Model\Complaint`, a class
| this application does not have; that one only worked because the gate falls
| back to guessing the policy from the class name.
|--------------------------------------------------------------------------
*/

describe('updating a complaint', function () {
    it('ignores a status supplied through the update endpoint', function () {
        $admin = actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $complaint->patient_id,
            'title' => 'تأخير في موعد الكشف رغم الحجز المسبق',
            'description' => 'انتظر المريض أكثر من ساعة دون أي توضيح من الإدارة.',
            'status' => 'closed',
            'resolution_note' => 'إغلاق مباشر من نموذج التعديل.',
        ])->assertRedirect(route('complaints.show', $complaint));

        // The text fields persist; the status and the note are discarded.
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'title' => 'تأخير في موعد الكشف رغم الحجز المسبق',
            'status' => 'new',
            'resolution_note' => null,
            'updated_by' => $admin->id,
        ]);
    });
});

describe('advancing a complaint', function () {
    it('records the acting administrator and moves the complaint forward', function () {
        $admin = actingAsAdmin();
        $assignee = User::factory()->create();
        $complaint = Complaint::factory()->unassigned()->create();

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.advance', $complaint), [
                'assigned_to' => $assignee->id,
            ])
            ->assertRedirect(route('complaints.show', $complaint));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'assigned',
            'assigned_to' => $assignee->id,
            'updated_by' => $admin->id,
        ]);
    });

    /*
    | A receptionist has no complaints.advance permission, but the policy also
    | lets the assignee move a complaint along once it is being investigated.
    | No factory state covers that status.
    */
    it('lets the assigned receptionist advance a complaint under investigation', function () {
        $receptionist = actingAsReceptionist();
        $complaint = Complaint::factory()->create([
            'status' => 'investigating',
            'assigned_to' => $receptionist->id,
        ]);

        $this->from(route('complaints.index'))
            ->patch(route('complaints.advance', $complaint), [
                'resolution_note' => 'تمت معالجة سبب الشكوى وإخطار المريض بالنتيجة.',
            ])
            ->assertRedirect(route('complaints.index'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
            'resolution_note' => 'تمت معالجة سبب الشكوى وإخطار المريض بالنتيجة.',
            'updated_by' => $receptionist->id,
        ]);
    });

    it('refuses to advance a complaint that is already closed', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->closed()->create();

        $this->from(route('complaints.index'))
            ->patch(route('complaints.advance', $complaint))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'closed',
        ]);
    });

    it('forbids a receptionist from advancing a complaint that is not theirs', function () {
        $receptionist = actingAsReceptionist();
        $complaint = Complaint::factory()->unassigned()->create();

        $this->from(route('complaints.index'))
            ->patch(route('complaints.advance', $complaint), [
                'assigned_to' => $receptionist->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'new',
            'assigned_to' => null,
        ]);
    });
});

/*
| The link was guarded by `@can('show', $patient)`, but `PatientPolicy` only
| defines `view`. The gate answered "no" for every user, so a complaint never
| offered a way into the patient file. The archived patient stays unlinked: that
| guard sits inside this one and is covered by PatientArchiveTest.
*/
describe('the patient file link on a complaint', function () {
    it('opens the patient file for the staff who may read it', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->create();

        $this->get(route('complaints.show', $complaint))
            ->assertOk()
            ->assertSee(route('patients.show', $complaint->patient));
    });
});

/*
| `App\Model\Complaint` is not a class in this application. The gate found the
| policy anyway by guessing it from the class name, so the button renders today.
| The test pins that behaviour down, because a rename would hide the button
| silently.
*/
describe('the manual complaint button', function () {
    it('is offered on the complaints index', function () {
        actingAsAdmin();
        Complaint::factory()->create();

        $this->get(route('complaints.index'))
            ->assertOk()
            ->assertSee(route('complaints.create'));
    });
});
