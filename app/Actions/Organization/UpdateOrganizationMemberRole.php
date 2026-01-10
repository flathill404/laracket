<?php

namespace App\Actions\Organization;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateOrganizationMemberRole
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Organization $organization, User $user, OrganizationRole $role): void
    {
        if ($organization->owner_user_id === $user->id) {
            throw ValidationException::withMessages([
                'user' => ['The organization owner\'s role cannot be changed.'],
            ]);
        }

        if (! $organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the organization.'],
            ]);
        }

        $organization->users()->updateExistingPivot($user->id, [
            'role' => $role->value,
        ]);
    }
}
