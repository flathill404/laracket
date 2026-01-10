<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->organizations()->where('organization_id', $organization->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isOrganizationOwner($organization) || $user->isAdmin($organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Admin is NOT allowed, only Owner
        return $user->isOrganizationOwner($organization);
    }

    /**
     * Determine whether the user can invite members to the organization.
     */
    public function invite_member(User $user, Organization $organization): bool
    {
        return $user->isOrganizationOwner($organization) || $user->isAdmin($organization);
    }
}
