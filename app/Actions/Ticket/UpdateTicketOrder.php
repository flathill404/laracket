<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class UpdateTicketOrder
{
    public function __invoke(Ticket $ticket, float $order): void
    {
        DB::transaction(function () use ($ticket, $order) {
            $ticket->update(['display_order' => $order]);
        });
    }
}
