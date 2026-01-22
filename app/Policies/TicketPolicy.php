<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        $project = $ticket->project;

        if ($user->isOrganizationOwner($project->organization) || $user->isAdmin($project->organization)) {
            return true;
        }

        return $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        // Check if user has create_ticket permission on the project
        // Using ProjectPolicy logic (approximated here to avoid circular dependencies/Facades)
        // ProjectPolicy::create_ticket -> view($project)

        // Replicating ProjectPolicy permission checks:
        if ($user->isOrganizationOwner($project->organization) || $user->isAdmin($project->organization)) {
            return true;
        }

        return $user->hasProjectAccess($project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        if ($user->isOrganizationOwner($ticket->project->organization) || $user->isAdmin($ticket->project->organization)) {
            return true;
        }

        // Reviewer check (Ticket creator/reviewer)
        return $ticket->reviewers->contains('id', $user->id);
    }

    /**
     * Determine whether the user can assign a user to the ticket.
     */
    public function assign_user(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    /**
     * Determine whether the user can unassign a user from the ticket.
     */
    public function unassign_user(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    /**
     * Determine whether the user can add a reviewer to the ticket.
     */
    public function add_reviewer(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    /**
     * Determine whether the user can remove a reviewer from the ticket.
     */
    public function remove_reviewer(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }
}
