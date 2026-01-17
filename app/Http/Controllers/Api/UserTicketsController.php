<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TicketResource;
use App\models\User;
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

        return TicketResource::collection($query($user));
    }
}
