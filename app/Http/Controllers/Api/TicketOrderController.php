<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketOrder;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TicketOrderController
{
    public function update(Request $request, Ticket $ticket, UpdateTicketOrder $action): Response
    {
        Gate::authorize('update', $ticket);

        $action($ticket, $request->float('order'));

        return response()->noContent();
    }
}
