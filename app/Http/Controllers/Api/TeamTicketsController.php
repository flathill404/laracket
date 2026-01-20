<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\Models\Team;
use App\Queries\GetTeamTickets;
use App\Values\TicketQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamTicketsController
{
    public function index(Request $request, Team $team, GetTeamTickets $query): AnonymousResourceCollection
    {
        if (! $request->user()?->teams->contains($team)) {
            abort(403);
        }

        $ticketQuery = new TicketQuery($request->query());

        $tickets = $query($team, $ticketQuery);

        return TicketResource::collection($tickets);
    }
}
