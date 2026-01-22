<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketStatus;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketStatusController
{
    public function update(Request $request, Ticket $ticket, UpdateTicketStatus $action): \Illuminate\Http\Response
    {
        Gate::authorize('update', $ticket);

        /** @var array{status: string|int} $validated */
        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\TicketStatus::class)],
        ]);

        $action($ticket, \App\Enums\TicketStatus::from($validated['status']));

        return response()->noContent();
    }
}
