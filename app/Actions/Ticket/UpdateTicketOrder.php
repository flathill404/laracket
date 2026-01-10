<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;

class UpdateTicketOrder
{
    public function __invoke(Ticket $ticket, int $order)
    {
        $ticket->update(['order' => $order]);
    }
}
