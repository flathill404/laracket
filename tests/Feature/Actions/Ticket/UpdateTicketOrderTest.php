<?php

use App\Actions\Ticket\UpdateTicketOrder;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('updates ticket order', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id, 'display_order' => 1.0]);

    $action = new UpdateTicketOrder;

    $action($ticket, 2.5);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'display_order' => 2.5,
    ]);
});
