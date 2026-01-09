<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTicket
{
    public function execute(User $actor, Ticket $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            // Here we could check permissions if $actor has right to delete
            return $ticket->delete();
        });
    }
}
