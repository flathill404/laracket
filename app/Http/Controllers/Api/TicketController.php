<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\CreateTicket;
use App\Actions\Ticket\DeleteTicket;
use App\Actions\Ticket\UpdateTicket;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Queries\GetProjectTickets;
use App\Queries\GetTicketDetail;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket>
     */
    public function index(Project $project, GetProjectTickets $query): \Illuminate\Support\Collection
    {
        return $query($project);
    }

    public function store(Request $request, Project $project, CreateTicket $action): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $ticket = $action($user, $project, $input);

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket, GetTicketDetail $query): Ticket
    {
        return $query($ticket);
    }

    public function update(Request $request, Ticket $ticket, UpdateTicket $action): \Illuminate\Http\JsonResponse
    {
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $ticket = $action($ticket, $input);

        return response()->json($ticket);
    }

    public function destroy(Request $request, Ticket $ticket, DeleteTicket $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $ticket);

        return response()->noContent();
    }
}
