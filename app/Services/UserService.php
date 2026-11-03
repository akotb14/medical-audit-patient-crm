<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * The number of users a dropdown loads before the staff list grows past it.
     * The currently selected user is always kept in the list.
     */
    private const OPTION_LIMIT = 500;

    /**
     * The columns an assignee dropdown renders.
     *
     * @var list<string>
     */
    private const OPTION_COLUMNS = ['id', 'name'];

    /**
     * The staff options offered by an assignee dropdown, bounded and ordered by
     * name.
     *
     * The user a complaint is already assigned to stays in the list even when the
     * staff list outgrew the limit: dropping it would leave the field pointing at
     * an option that is not there, and the browser would submit whichever name
     * happened to be first instead.
     *
     * @return Collection<int, User>
     */
    public function getAssigneeOptions(?int $selectedUserId = null): Collection
    {
        $users = $this->optionQuery()
            ->limit(self::OPTION_LIMIT)
            ->get();

        if ($selectedUserId === null || $users->contains('id', $selectedUserId)) {
            return $users;
        }

        $selectedUser = $this->optionQuery()->find($selectedUserId);

        if ($selectedUser === null) {
            return $users;
        }

        return $users
            ->push($selectedUser)
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Builder<User>
     */
    private function optionQuery(): Builder
    {
        return User::query()
            ->select(self::OPTION_COLUMNS)
            ->with('roles:id,name')
            ->orderBy('name');
    }
}
