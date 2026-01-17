<?php

namespace App\Queries;

use App\Models\Ticket;

class GetTicketDetail
{
    public function __invoke(Ticket $ticket): Ticket
    {
        $ticket->load('assignees');
        $ticket->load('reviewers');
        $ticket->load('comments');

        return $ticket;
    }
}
