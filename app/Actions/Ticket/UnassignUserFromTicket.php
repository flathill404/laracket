<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;

class UnassignUserFromTicket
{
    public function __invoke(Ticket $ticket, User $user): void
    {
        $ticket->assignees()->detach($user);
    }
}
