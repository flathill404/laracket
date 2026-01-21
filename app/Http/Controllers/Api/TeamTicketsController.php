<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\Models\Team;
use App\Queries\GetTeamTickets;
use App\Values\TicketQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TeamTicketsController
{
    public function index(Request $request, Team $team, GetTeamTickets $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $team);

        $ticketQuery = new TicketQuery((array) $request->query());

        $tickets = $query($team, $ticketQuery);

        return TicketResource::collection($tickets);
    }
}
