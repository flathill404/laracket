<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\CreateTicket;
use App\Actions\Ticket\DeleteTicket;
use App\Actions\Ticket\UpdateTicket;
use App\Http\Resources\TicketResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Queries\GetProjectTickets;
use App\Queries\GetTicketDetail;
use App\Values\TicketQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TicketController
{
    public function index(Request $request, Project $project, GetProjectTickets $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $ticketQuery = new TicketQuery((array) $request->query());

        $tickets = $query($project, $ticketQuery);

        return TicketResource::collection($tickets);
    }

    public function store(Request $request, Project $project, CreateTicket $action): JsonResponse
    {
        Gate::authorize('create_ticket', $project);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $ticket = $action($user, $project, $input);

        return response()->json(new TicketResource($ticket), 201);
    }

    public function show(Organization $organization, Project $project, Ticket $ticket, GetTicketDetail $query): TicketResource
    {
        Gate::authorize('view', $ticket);

        $ticket = $query($ticket);

        return new TicketResource($ticket);
    }

    public function update(Request $request, Ticket $ticket, UpdateTicket $action, GetTicketDetail $query): TicketResource
    {
        Gate::authorize('update', $ticket);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $action($ticket, $input);

        $updatedTicket = $query($ticket);

        return new TicketResource($updatedTicket);
    }

    public function destroy(Request $request, Ticket $ticket, DeleteTicket $action): Response
    {
        Gate::authorize('delete', $ticket);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $action($user, $ticket);

        return response()->noContent();
    }
}
