<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\AddReviewerToTicket;
use App\Actions\Ticket\RemoveReviewerFromTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketReviewerController
{
    public function store(Request $request, Ticket $ticket, AddReviewerToTicket $action): \Illuminate\Http\Response
    {
        Gate::authorize('add_reviewer', $ticket);

        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($ticket, $user);

        return response()->noContent();
    }

    public function destroy(Ticket $ticket, User $user, RemoveReviewerFromTicket $action): \Illuminate\Http\Response
    {
        Gate::authorize('remove_reviewer', $ticket);

        $action($ticket, $user);

        return response()->noContent();
    }
}
