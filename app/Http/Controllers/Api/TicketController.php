<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\CreateTicket;
use App\Actions\Ticket\DeleteTicket;
use App\Actions\Ticket\UpdateTicket;
use App\Http\Resources\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Queries\GetProjectTickets;
use App\Queries\GetTicketDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController
{
    public function index(Project $project, GetProjectTickets $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $tickets = $query($project);

        return TicketResource::collection($tickets);
    }

    public function store(Request $request, Project $project, CreateTicket $action): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('create_ticket', $project);

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $ticket = $action($user, $project, $input);

        return response()->json(new TicketResource($ticket), 201);
    }

    public function show(Ticket $ticket, GetTicketDetail $query): TicketResource
    {
        Gate::authorize('view', $ticket);

        $ticket = $query($ticket);

        return new TicketResource($ticket);
    }

    public function update(Request $request, Ticket $ticket, UpdateTicket $action): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('update', $ticket);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $ticket = $action($ticket, $input);

        return response()->json(new TicketResource($ticket));
    }

    public function destroy(Request $request, Ticket $ticket, DeleteTicket $action): \Illuminate\Http\Response
    {
        Gate::authorize('delete', $ticket);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $ticket);

        return response()->noContent();
    }
}
