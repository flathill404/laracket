<?php

declare(strict_types=1);

namespace App\Actions\Team;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddTeamMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Team $team, User $user, TeamRole $role = TeamRole::Member): void
    {
        $this->validateUserBelongsToOrganization($team, $user);
        $this->validateUserIsNotMember($team, $user);

        DB::transaction(function () use ($team, $user, $role) {
            // RoleはEnumの値(value)を渡すのが確実じゃ
            $team->users()->attach($user, ['role' => $role->value]);
        });
    }

    /**
     * Check if user belongs to the organization
     *
     * @throws ValidationException
     */
    protected function validateUserBelongsToOrganization(Team $team, User $user): void
    {
        if (! $team->organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user does not belong to the team organization.'],
            ]);
        }
    }

    /**
     * Check if user is already a member of the team
     *
     * @throws ValidationException
     */
    protected function validateUserIsNotMember(Team $team, User $user): void
    {
        if ($team->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already a member of the team.'],
            ]);
        }
    }
}
