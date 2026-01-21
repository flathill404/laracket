<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\Models\User;
use App\Queries\GetUserTickets;
use App\Values\TicketQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class UserTicketsController
{
    public function index(Request $request, User $user, GetUserTickets $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $user);

        $ticketQuery = new TicketQuery((array) $request->query());

        $tickets = $query($user, $ticketQuery);

        return TicketResource::collection($tickets);
    }
}
