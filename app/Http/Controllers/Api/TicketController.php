<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\CreateTicket;
use App\Actions\Ticket\DeleteTicket;
use App\Actions\Ticket\UpdateTicketContent;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Queries\GetProjectTickets;
use App\Queries\GetTicketDetail;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Project $project, GetProjectTickets $query)
    {
        return $query($project);
    }

    public function store(Request $request, Project $project, CreateTicket $action)
    {
        $ticket = $action($project, $request->user(), $request->all());

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket, GetTicketDetail $query)
    {
        return $query($ticket);
    }

    public function update(Request $request, Ticket $ticket, UpdateTicketContent $action)
    {
        $ticket = $action($ticket, $request->all());

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket, DeleteTicket $action)
    {
        $action($ticket);

        return response()->noContent();
    }
}
