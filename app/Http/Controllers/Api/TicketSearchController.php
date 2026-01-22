<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\Queries\SearchTickets;
use App\Values\TicketSearchQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketSearchController
{
    public function index(Request $request, SearchTickets $query): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $searchQuery = new TicketSearchQuery((array) $request->query());

        $tickets = $query($user, $searchQuery);

        return TicketResource::collection($tickets);
    }
}
