<?php

namespace App\Actions\Team;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddTeamMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Team $team, User $user, TeamRole $role = TeamRole::Member): void
    {
        // Check if user belongs to the organization
        if (! $team->organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user does not belong to the team organization.'],
            ]);
        }

        // Check if user is already a member of the team
        if ($team->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already a member of the team.'],
            ]);
        }

        $team->users()->attach($user, ['role' => $role]);
    }
}
