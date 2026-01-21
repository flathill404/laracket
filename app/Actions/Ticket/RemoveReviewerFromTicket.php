<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveReviewerFromTicket
{
    public function __invoke(Ticket $ticket, User $user): void
    {
        DB::transaction(function () use ($ticket, $user) {
            $ticket->reviewers()->detach($user);
        });
    }
}
