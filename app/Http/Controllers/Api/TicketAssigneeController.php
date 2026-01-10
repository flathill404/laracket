<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\AssignUserToTicket;
use App\Actions\Ticket\UnassignUserFromTicket;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketAssigneeController extends Controller
{
    public function store(Request $request, Ticket $ticket, AssignUserToTicket $action)
    {
        $action($ticket, User::find($request->input('user_id')));

        return response()->noContent();
    }

    public function destroy(Ticket $ticket, User $user, UnassignUserFromTicket $action)
    {
        $action($ticket, $user);

        return response()->noContent();
    }
}
