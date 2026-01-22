<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOrganizationMemberRole
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Organization $organization, User $user, OrganizationRole $role): void
    {
        $this->validateUserIsNotOwner($organization, $user);
        $this->validateUserIsMember($organization, $user);

        DB::transaction(function () use ($organization, $user, $role) {
            $organization->users()->updateExistingPivot($user->id, [
                'role' => $role->value,
            ]);
        });
    }

    /**
     * @throws ValidationException
     */
    protected function validateUserIsNotOwner(Organization $organization, User $user): void
    {
        if ($organization->owner_user_id === $user->id) {
            throw ValidationException::withMessages([
                'user' => ['The organization owner\'s role cannot be changed.'],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function validateUserIsMember(Organization $organization, User $user): void
    {
        if (! $organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the organization.'],
            ]);
        }
    }
}
