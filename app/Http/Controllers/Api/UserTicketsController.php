<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Resources\TicketResource;
use App\Models\User;
use App\Queries\GetUserTickets;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserTicketsController
{
    public function index(Request $request, User $user, GetUserTickets $query): AnonymousResourceCollection
    {
        if ($request->user()?->id !== $user->id) {
            abort(403);
        }

        /** @var string|array<string>|null $statusInput */
        $statusInput = $request->input('status');
        $statuses = TicketStatus::fromValues($statusInput);

        return TicketResource::collection($query($user, $statuses));
    }
}
