<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\User;
use App\Queries\ComplaintQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComplaintService
{
    public function __construct(private UserService $userService , private ComplaintQuery $complaintQuery) {}


    public function paginateIndex(array $filters): array
    {
        $query = Complaint::with(['patient', 'survey.visit', 'assignee.roles', 'creator'])
            ->latest('created_at');
        return [
            'complaints' => $this->complaintQuery->getIndexQuery($query,$filters)->paginate(15)->withQueryString(),
            'stats' => $this->complaintQuery->getStatistics(),
            'users' => $this->userService->getAssigneeOptions(
                is_numeric($filters['assigned_to'] ?? null) ? (int) $filters['assigned_to'] : null
            ),
        ];
    }


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


    public function update(Complaint $complaint, array $data, ?int $actorId): void
    {
        $complaint->update([
            ...$data,
            'updated_by' => $actorId,
        ]);
    }


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
