<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketActivityResource;
use App\Models\Ticket;
use App\Queries\GetTicketActivities;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TicketActivityController
{
    public function index(Ticket $ticket, GetTicketActivities $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $ticket->project);

        $activities = $query($ticket);

        return TicketActivityResource::collection($activities);
    }
}
