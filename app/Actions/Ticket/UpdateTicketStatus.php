<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatus
{
    public function __invoke(Ticket $ticket, TicketStatus $status): Ticket
    {
        DB::transaction(function () use ($ticket, $status) {
            $ticket->update(['status' => $status->value]);
        });

        return $ticket;
    }
}
