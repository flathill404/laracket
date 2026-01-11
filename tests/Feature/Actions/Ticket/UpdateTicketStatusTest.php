<?php

use App\Actions\Ticket\UpdateTicketStatus;
use App\Enums\TicketStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('updates ticket status', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id, 'status' => TicketStatus::Open]);

    $action = new UpdateTicketStatus;

    $action($ticket, TicketStatus::InProgress);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => TicketStatus::InProgress->value,
    ]);
});
