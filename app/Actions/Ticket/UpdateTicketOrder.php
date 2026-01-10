<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;

class UpdateTicketOrder
{
    public function __invoke(Ticket $ticket, float $order): void
    {
        $ticket->update(['display_order' => $order]);
    }
}
