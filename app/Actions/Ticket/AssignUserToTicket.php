<?php

namespace App\Actions\Ticket;

use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AssignUserToTicket
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Ticket $ticket, User $user): void
    {
        // Check if user is a member of the project
        if (! $ticket->project->assignedUsers()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the project.'],
            ]);
        }

        // Check if user is already assigned to the ticket
        if ($ticket->assignees()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already assigned to the ticket.'],
            ]);
        }

        $ticket->assignees()->attach($user, ['type' => TicketUserType::Assignee]);
    }
}
