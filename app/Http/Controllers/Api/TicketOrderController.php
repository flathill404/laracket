<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketOrder;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketOrderController extends Controller
{
    public function update(Request $request, Ticket $ticket, UpdateTicketOrder $action)
    {
        $action($ticket, $request->input('order'));

        return response()->noContent();
    }
}
