<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatus
{
    public function update(User $actor, Ticket $ticket, TicketStatus $status): Ticket
    {
        return DB::transaction(function () use ($ticket, $status) {
            $ticket->update(['status' => $status]);

            return $ticket;
        });
    }
}
