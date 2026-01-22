<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\UpdateTicketStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketStatusController
{
    public function update(Request $request, Ticket $ticket, UpdateTicketStatus $action): Response
    {
        Gate::authorize('update', $ticket);

        /** @var array{status: string|int} $validated */
        $validated = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
        ]);

        $action($ticket, TicketStatus::from($validated['status']));

        return response()->noContent();
    }
}
