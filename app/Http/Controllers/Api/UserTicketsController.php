<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\Models\User;
use App\Queries\GetUserTickets;
use App\Values\TicketQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserTicketsController
{
    public function index(Request $request, User $user, GetUserTickets $query): AnonymousResourceCollection
    {
        if ($request->user()?->id !== $user->id) {
            abort(403);
        }

        $ticketQuery = new TicketQuery((array) $request->query());

        $tickets = $query($user, $ticketQuery);

        return TicketResource::collection($tickets);
    }
}
