<?php

declare(strict_types=1);

namespace App\Actions\Team;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTeamMemberRole
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Team $team, User $user, TeamRole $role): void
    {
        $this->validateUserIsMember($team, $user);

        DB::transaction(function () use ($team, $user, $role) {
            $team->users()->updateExistingPivot($user->id, ['role' => $role->value]);
        });
    }

    /**
     * @throws ValidationException
     */
    protected function validateUserIsMember(Team $team, User $user): void
    {
        if (! $team->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the team.'],
            ]);
        }
    }
}
