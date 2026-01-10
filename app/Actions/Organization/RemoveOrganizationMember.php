<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveOrganizationMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Organization $organization, User $user): void
    {
        if ($organization->owner_user_id === $user->id) {
            throw ValidationException::withMessages([
                'user' => ['The organization owner cannot be removed.'],
            ]);
        }

        $organization->users()->detach($user);
    }
}
