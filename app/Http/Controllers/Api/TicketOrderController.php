<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketOrder;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketOrderController
{
    public function update(Request $request, Ticket $ticket, UpdateTicketOrder $action): \Illuminate\Http\Response
    {
        Gate::authorize('update', $ticket);

        $action($ticket, $request->float('order'));

        return response()->noContent();
    }
}
