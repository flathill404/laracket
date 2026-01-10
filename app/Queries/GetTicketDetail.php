<?php

namespace App\Queries;

use App\Models\Ticket;

class GetTicketDetail
{
    public function execute(Ticket $ticket)
    {
        return $ticket;
    }
}
