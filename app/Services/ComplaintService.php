<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComplaintService
{
    public function __construct(private UserService $userService) {}

    /**
     * The complaint register, filtered by the request.
     *
     * The tiles describe the whole register, so they are read from a clone of
     * the query before the filters are applied to the listing.
     *
     * @param  array<string, mixed>  $filters
     * @return array{complaints: LengthAwarePaginator, stats: object|null, users: Collection<int, User>}
     */
    public function paginateIndex(array $filters): array
    {
        $query = Complaint::with(['patient', 'survey.visit', 'assignee.roles', 'creator'])
            ->latest('created_at');

        // The tiles are one aggregate row: the relations the listing eager loads
        // have nothing to attach to and would only run discarded queries.
        $stats = (clone $query)->setEagerLoads([])->stats()->first();

        $query->filter($filters);

        return [
            'complaints' => $query->paginate(15)->withQueryString(),
            'stats' => $stats,
            'users' => $this->userService->getAssigneeOptions(
                is_numeric($filters['assigned_to'] ?? null) ? (int) $filters['assigned_to'] : null
            ),
        ];
    }

    /**
     * File a manually recorded complaint.
     *
     * A complaint created with an assignee starts its workflow at `assigned`, so
     * the record never claims to be new while naming one. The controller checks
     * the assign ability before calling this: assigning at creation takes the
     * same ability as the dedicated assign action.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $actorId): Complaint
    {
        $data['source'] = 'manual';
        $data['created_by'] = $actorId;

        if (! empty($data['assigned_to'])) {
            $data['status'] = 'assigned';
        } else {
            $data['assigned_to'] = null;
            $data['status'] = 'new';
        }

        return Complaint::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Complaint $complaint, array $data, ?int $actorId): void
    {
        $complaint->update([
            ...$data,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * Move a complaint to the stage that follows the one it is in.
     *
     * The stage is read under the row lock, and the input required to enter that
     * stage is validated against the locked state. The views read the stage
     * timestamps, so the moment a complaint reaches a stage is stored with the
     * stage; a stage it already reached keeps its original timestamp.
     *
     * @param  array<string, mixed>  $input
     */
    public function advance(Complaint $complaint, array $input, ?int $actorId): void
    {
        DB::transaction(function () use ($complaint, $input, $actorId): void {
            $lockedComplaint = Complaint::query()
                ->lockForUpdate()
                ->findOrFail($complaint->id);

            $nextStatus = $lockedComplaint->nextStatus();

            if ($nextStatus === null) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن الانتقال من الحالة الحالية.',
                ]);
            }

            $rules = match ($lockedComplaint->status) {
                'new' => [
                    'assigned_to' => [
                        'required',
                        'integer',
                        'exists:users,id',
                    ],
                ],

                'investigating' => [
                    'resolution_note' => [
                        'required',
                        'string',
                        'max:2000',
                    ],
                ],

                default => [],
            };

            $data = Validator::make($input, $rules)->validate();

            $timestamps = match ($nextStatus) {
                'resolved' => ['resolved_at' => $lockedComplaint->resolved_at ?? now()],
                'closed' => ['closed_at' => $lockedComplaint->closed_at ?? now()],
                default => [],
            };

            $lockedComplaint->update([
                ...$data,
                ...$timestamps,
                'status' => $nextStatus,
                'updated_by' => $actorId,
            ]);
        });
    }

    /**
     * Assign a new complaint, returning the name of the assignee that was stored.
     *
     * The status is checked again after the row is locked: a concurrent
     * assignment or advance must not both pass a check made on the copy the
     * route resolved. The name comes from the locked row, so it always names the
     * assignee that was actually stored.
     *
     * @param  array{assigned_to: int|string}  $data
     */
    public function assign(Complaint $complaint, array $data, ?int $actorId): string
    {
        return DB::transaction(function () use ($complaint, $data, $actorId): string {
            $lockedComplaint = Complaint::query()
                ->whereKey($complaint->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedComplaint->status !== 'new') {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن إسناد الشكوى في حالتها الحالية.',
                ]);
            }

            $lockedComplaint->update([
                'assigned_to' => $data['assigned_to'],
                'status' => 'assigned',
                'updated_by' => $actorId,
            ]);

            return (string) $lockedComplaint->assignee()->value('name');
        });
    }

    /**
     * Put a resolved or closed complaint back under investigation.
     *
     * A reopened complaint goes back under investigation, so the moments it
     * reached `resolved` and `closed` no longer describe the stage it is in and
     * are cleared with the status. The status is re-checked after the row is
     * locked, so a concurrent reopen or advance cannot both pass.
     *
     * @return bool whether the complaint was reopened
     */
    public function reopen(Complaint $complaint, ?int $actorId): bool
    {
        $reopened = DB::transaction(function () use ($complaint, $actorId): bool {
            $lockedComplaint = Complaint::query()
                ->whereKey($complaint->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedComplaint->canReopen()) {
                return false;
            }

            $lockedComplaint->update([
                'status' => 'investigating',
                'resolved_at' => null,
                'closed_at' => null,
                'updated_by' => $actorId,
            ]);

            return true;
        });

        if (! $reopened) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن إعادة فتح الشكوى في حالتها الحالية.',
            ]);
        }

        return true;
    }

    public function delete(Complaint $complaint): void
    {
        $complaint->delete();
    }
}
