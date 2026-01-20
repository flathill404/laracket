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

        // Validation for per_page
        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 1) {
            $perPage = 1;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        /** @var string|null $sort */
        $sort = $request->input('sort');

        return TicketResource::collection($query($team, $statuses, $sort, $perPage));
    }
}
