<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;

class AssignUserToTicket
{
    public function __invoke(Ticket $ticket, User $user)
    {
        // Assign logic
    }
}
