<?php

use App\Models\Complaint;
use App\Models\Patient;
use App\Models\User;

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

    /*
    | The assignee picker names the role of every account in the list. The role
    | lives on the account, not on the users table, so the page has to read it
    | from the account instead of printing a fixed label for everyone.
    */
    it('renders the create form with the role of every assignee', function () {
        $admin = actingAsAdmin();
        $patient = Patient::factory()->create(['fullname' => 'منى عبد الرحمن']);

        $this->get(route('complaints.create'))
            ->assertOk()
            ->assertSee($patient->fullname)
            ->assertSee($admin->name.' - '.__('roles.'.$admin->roleNames()));
    });
});

/*
| The stage badge on the edit screen is painted by the accessor of the complaint
| itself. The view named an accessor that does not exist, and Eloquent renders a
| missing attribute as an empty string instead of failing, so the badge lost its
| colours without any page error.
*/
describe('the stage badge on the complaint edit screen', function () {
    it('paints the badge of the stage the complaint is in', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->create(['status' => 'investigating']);

        $this->get(route('complaints.edit', $complaint))
            ->assertOk()
            ->assertSee($complaint->status_badge_classes)
            ->assertSee('bg-amber-50 text-amber-800 border-amber-200');
    });
});

/*
| The stage timestamps are printed by the complaint screens, so the moment a
| complaint reaches a stage has to be stored with the stage instead of being
| left null, and a stage it already reached keeps its original moment.
*/
describe('the stage timestamps of a complaint', function () {
    it('records the moment the complaint was resolved', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->create([
            'status' => 'investigating',
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.advance', $complaint), [
                'resolution_note' => 'تمت معالجة سبب الشكوى وإخطار المريض بالنتيجة.',
            ])
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();

        expect($complaint->status)->toBe('resolved')
            ->and($complaint->resolved_at)->not->toBeNull()
            ->and($complaint->closed_at)->toBeNull();
    });

    it('keeps the resolution moment when the complaint is closed', function () {
        actingAsAdmin();
        $resolvedAt = now()->subDay()->startOfSecond();
        $complaint = Complaint::factory()->create([
            'status' => 'resolved',
            'resolved_at' => $resolvedAt,
            'closed_at' => null,
        ]);

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.advance', $complaint))
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();

        expect($complaint->status)->toBe('closed')
            ->and($complaint->closed_at)->not->toBeNull()
            ->and($complaint->resolved_at?->equalTo($resolvedAt))->toBeTrue();
    });
});

/*
| The create endpoint refuses an archived patient. The update endpoint has to
| agree, while still allowing a complaint whose patient was archived afterwards
| to be corrected without moving it.
*/
describe('the patient of a complaint', function () {
    /*
    | A complaint records one clinical event for one patient. Moving it to a
    | live file detaches a survey-sourced complaint from the survey's patient
    | and makes the patient history describe two different people.
    */
    it('refuses to move a complaint to another patient', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();
        $otherPatient = Patient::factory()->create();

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $otherPatient->id,
            'title' => $complaint->title,
            'description' => $complaint->description,
        ])->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'patient_id' => $complaint->patient_id,
        ]);
    });

    it('refuses to move a complaint to an archived patient file', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();
        $archived = Patient::factory()->create();
        $archived->delete();

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $archived->id,
            'title' => $complaint->title,
            'description' => $complaint->description,
        ])->assertSessionHasErrors('patient_id');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'patient_id' => $complaint->patient_id,
        ]);
    });

    it('saves a complaint whose patient was archived afterwards', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();
        $complaint->patient->delete();

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $complaint->patient_id,
            'title' => 'عنوان مصحح لشكوى قديمة',
            'description' => $complaint->description,
        ])->assertRedirect(route('complaints.show', $complaint))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'patient_id' => $complaint->patient_id,
            'title' => 'عنوان مصحح لشكوى قديمة',
        ]);
    });
});

describe('the stored length of the complaint title', function () {
    it('accepts a title as long as the stored column', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();
        $title = str_repeat('م', 255);

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $complaint->patient_id,
            'title' => $title,
            'description' => $complaint->description,
        ])->assertRedirect(route('complaints.show', $complaint))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'title' => $title]);
    });

    it('refuses a title longer than the stored column', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();

        $this->put(route('complaints.update', $complaint), [
            'patient_id' => $complaint->patient_id,
            'title' => str_repeat('م', 256),
            'description' => $complaint->description,
        ])->assertSessionHasErrors('title');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'title' => $complaint->title,
        ]);
    });
});

/*
| Reopening puts the complaint back under investigation, so the moments it
| reached `resolved` and `closed` stop describing the stage it is in. The
| factory states are asserted first: a complaint that never held those moments
| would make the check below pass without the controller doing anything.
*/
describe('reopening a complaint', function () {
    it('clears the moments of a complaint that was resolved', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->resolved()->create();

        expect($complaint->resolved_at)->not->toBeNull()
            ->and($complaint->closed_at)->toBeNull();

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.reopen', $complaint))
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();

        expect($complaint->status)->toBe('investigating')
            ->and($complaint->resolved_at)->toBeNull()
            ->and($complaint->closed_at)->toBeNull();
    });

    it('clears the moments of a complaint that was closed', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->closed()->create();

        expect($complaint->resolved_at)->not->toBeNull()
            ->and($complaint->closed_at)->not->toBeNull();

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.reopen', $complaint))
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();

        expect($complaint->status)->toBe('investigating')
            ->and($complaint->resolved_at)->toBeNull()
            ->and($complaint->closed_at)->toBeNull();
    });
});

/*
| The register used to read `$request->query()` directly, so an array in a
| filter reached `where('status', ['new'])` or an array-to-string conversion in
| the `LIKE` clause and the page answered with a 500.
*/
describe('the complaint register filters', function () {
    it('rejects a filter supplied as an array instead of failing', function () {
        actingAsAdmin();

        $this->from(route('complaints.index'))
            ->get(route('complaints.index', ['status' => ['new']]))
            ->assertRedirect(route('complaints.index'))
            ->assertSessionHasErrors('status');
    });

    it('accepts the filters the register offers', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->unassigned()->create();

        $this->get(route('complaints.index', [
            'status' => 'all',
            'source' => 'all',
            'assigned_to' => 'unassigned',
            'search' => $complaint->title,
        ]))->assertOk();
    });
});

/*
| A complaint created with an assignee starts at `assigned`; without one it
| starts at `new`. The controller used to force `new` either way, so a record
| could name an assignee while the workflow said nobody had been assigned yet.
*/
describe('creating a complaint', function () {
    it('starts an unassigned complaint at the new stage', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'شكوى بدون مسند إليه',
            'description' => 'تفاصيل الشكوى.',
        ])->assertRedirect(route('complaints.index'));

        $this->assertDatabaseHas('complaints', [
            'patient_id' => $patient->id,
            'status' => 'new',
            'assigned_to' => null,
        ]);
    });

    it('starts an assigned complaint at the assigned stage', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();
        $assignee = User::factory()->create();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'شكوى مسندة من البداية',
            'description' => 'تفاصيل الشكوى.',
            'assigned_to' => $assignee->id,
        ])->assertRedirect(route('complaints.index'));

        $complaint = Complaint::query()->sole();

        expect($complaint->status)->toBe('assigned')
            ->and($complaint->assigned_to)->toBe($assignee->id);
    });

    it('rejects an assignee that does not exist', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'شكوى بمسند غير موجود',
            'description' => 'تفاصيل الشكوى.',
            'assigned_to' => 999999,
        ])->assertSessionHasErrors('assigned_to');

        expect(Complaint::query()->exists())->toBeFalse();
    });

    it('refuses creation with an assignee for an actor who may not assign', function () {
        actingAsReceptionist();
        $patient = Patient::factory()->create();
        $assignee = User::factory()->create();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'شكوى بمسند من غير المخول',
            'description' => 'تفاصيل الشكوى.',
            'assigned_to' => $assignee->id,
        ])->assertForbidden();

        expect(Complaint::query()->exists())->toBeFalse();
    });
});

describe('the complaint action feedback', function () {
    it('names the stored assignee in the assignment confirmation', function () {
        actingAsAdmin();
        $assignee = User::factory()->create(['name' => 'منى عبد الرحمن']);
        $complaint = Complaint::factory()->unassigned()->create();

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.assign', $complaint), ['assigned_to' => $assignee->id])
            ->assertRedirect(route('complaints.show', $complaint))
            ->assertSessionHas('success', 'تم إسناد الشكوى إلى: منى عبد الرحمن');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'assigned',
            'assigned_to' => $assignee->id,
        ]);
    });

    it('refuses to assign a complaint that moved past the new stage', function () {
        actingAsAdmin();
        $assignee = User::factory()->create();
        $complaint = Complaint::factory()->create(['status' => 'investigating']);

        $this->from(route('complaints.show', $complaint))
            ->patch(route('complaints.assign', $complaint), ['assigned_to' => $assignee->id])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('complaints', [
            'id' => $complaint->id,
            'assigned_to' => $assignee->id,
        ]);
    });

    it('confirms the creation of a complaint', function () {
        actingAsAdmin();
        $patient = Patient::factory()->create();

        $this->post(route('complaints.store'), [
            'patient_id' => $patient->id,
            'title' => 'شكوى جديدة',
            'description' => 'تفاصيل الشكوى.',
        ])->assertRedirect(route('complaints.index'))
            ->assertSessionHas('success');
    });

    it('confirms the deletion of a complaint', function () {
        actingAsAdmin();
        $complaint = Complaint::factory()->create();

        $this->delete(route('complaints.destroy', $complaint))
            ->assertRedirect(route('complaints.index'))
            ->assertSessionHas('success');
    });
});
