<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // Allowed if user is a member of the organization
        return $user->organizations()->where('organization_id', $team->organization_id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->isOrganizationOwner($team->organization) ||
            $user->isAdmin($team->organization) ||
            $user->isTeamLeader($team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Only Org Owner or Admin
        return $user->isOrganizationOwner($team->organization) ||
            $user->isAdmin($team->organization);
    }

    /**
     * Determine whether the user can add members to the team.
     */
    public function add_member(User $user, Team $team): bool
    {
        return $this->update($user, $team);
    }

    /**
     * Determine whether the user can remove members from the team.
     */
    public function remove_member(User $user, Team $team): bool
    {
        return $this->update($user, $team);
    }

    /**
     * Determine whether the user can update member roles in the team.
     */
    public function update_member_role(User $user, Team $team): bool
    {
        return $this->update($user, $team);
    }
}
