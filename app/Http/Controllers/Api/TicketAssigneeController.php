<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\AssignUserToTicket;
use App\Actions\Ticket\UnassignUserFromTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TicketAssigneeController
{
    public function store(Request $request, Ticket $ticket, AssignUserToTicket $action): Response
    {
        Gate::authorize('assign_user', $ticket);

        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($ticket, $user);

        return response()->noContent();
    }

    public function destroy(Ticket $ticket, User $user, UnassignUserFromTicket $action): Response
    {
        Gate::authorize('unassign_user', $ticket);

        $action($ticket, $user);

        return response()->noContent();
    }
}
