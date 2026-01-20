<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Resources\TicketResource;
use App\Models\Team;
use App\Queries\GetTeamTickets;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamTicketsController
{
    public function index(Request $request, Team $team, GetTeamTickets $query): AnonymousResourceCollection
    {
        if (! $request->user()?->teams->contains($team)) {
            abort(403);
        }

        /** @var string|array<string>|null $statusInput */
        $statusInput = $request->input('status');
        $statuses = TicketStatus::fromValues($statusInput);

        return TicketResource::collection($query($team, $statuses));
    }
}
