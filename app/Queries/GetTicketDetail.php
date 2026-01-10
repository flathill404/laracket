<?php

namespace App\Queries;

use App\Models\Ticket;

class GetTicketDetail
{
    public function __invoke(Ticket $ticket): Ticket
    {
        return $ticket;
    }
}
