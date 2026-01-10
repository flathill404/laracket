<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketOrder;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketOrderController extends Controller
{
    public function update(Request $request, Ticket $ticket, UpdateTicketOrder $action): \Illuminate\Http\Response
    {
        $action($ticket, $request->float('order'));

        return response()->noContent();
    }
}
