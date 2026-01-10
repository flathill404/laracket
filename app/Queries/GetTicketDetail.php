<?php

namespace App\Queries;

use App\Models\Ticket;

class GetTicketDetail extends Query
{
    public function execute(Ticket $ticket)
    {
        return $ticket;
    }
}
