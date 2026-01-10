<?php

namespace App\Actions\Team;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateTeamMemberRole
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Team $team, User $user, TeamRole $role): void
    {
        if (! $team->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the team.'],
            ]);
        }

        $team->users()->updateExistingPivot($user->id, ['role' => $role]);
    }
}
