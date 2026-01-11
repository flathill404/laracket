<?php

namespace App\Queries;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class GetTicketComments
{
    /**
     * @return Collection<int, \App\Models\Comment>
     */
    public function __invoke(Ticket $ticket): Collection
    {
        return $ticket->comments()
            ->with('user')
            ->get();
    }
}
