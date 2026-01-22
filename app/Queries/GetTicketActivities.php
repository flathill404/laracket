<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class GetTicketActivities
{
    /**
     * @return Collection<int, \App\Models\TicketActivity>
     */
    public function __invoke(Ticket $ticket): Collection
    {
        return $ticket->activities()
            ->with('user')
            ->get();
    }
}
