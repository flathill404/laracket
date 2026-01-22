<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->isOrganizationOwner($project->organization) || $user->isAdmin($project->organization)) {
            return true;
        }

        return $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->isOrganizationOwner($project->organization) || $user->isAdmin($project->organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isOrganizationOwner($project->organization) || $user->isAdmin($project->organization);
    }

    /**
     * Determine whether the user can create tickets for the project.
     */
    public function create_ticket(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    /**
     * Determine whether the user can add members to the project.
     */
    public function add_member(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can remove members from the project.
     */
    public function remove_member(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can attach teams to the project.
     */
    public function attach_team(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can detach teams from the project.
     */
    public function detach_team(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
