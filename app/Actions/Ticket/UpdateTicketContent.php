<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;

class UpdateTicketContent
{
    public function __invoke(Ticket $ticket, array $data)
    {
        $ticket->update($data);

        return $ticket;
    }
}
