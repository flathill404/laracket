<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddReviewerToTicket
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Ticket $ticket, User $user): void
    {
        $this->validateUserIsMemberOfProject($ticket, $user);
        $this->validateUserIsNotReviewer($ticket, $user);

        DB::transaction(function () use ($ticket, $user) {
            $ticket->reviewers()->attach($user, ['type' => TicketUserType::Reviewer->value]);
        });
    }

    /**
     * Check if user is a member of the project
     *
     * @throws ValidationException
     */
    protected function validateUserIsMemberOfProject(Ticket $ticket, User $user): void
    {
        if (! $ticket->project->hasMember($user)) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of the project.'],
            ]);
        }
    }

    /**
     * Check if user is already a reviewer of the ticket
     *
     * @throws ValidationException
     */
    protected function validateUserIsNotReviewer(Ticket $ticket, User $user): void
    {
        if ($ticket->reviewers()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already a reviewer of the ticket.'],
            ]);
        }
    }
}
