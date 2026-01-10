<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketStatusController extends Controller
{
    public function update(Request $request, Ticket $ticket, UpdateTicketStatus $action)
    {
        $action($ticket, $request->input('status'));

        return response()->noContent();
    }
}
