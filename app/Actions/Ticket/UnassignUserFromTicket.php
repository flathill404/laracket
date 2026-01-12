<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UnassignUserFromTicket
{
    public function __invoke(Ticket $ticket, User $user): void
    {
        DB::transaction(function () use ($ticket, $user) {
            $ticket->assignees()->detach($user);
        });
    }
}
