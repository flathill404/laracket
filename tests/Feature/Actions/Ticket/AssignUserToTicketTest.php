<?php

use App\Actions\Ticket\AssignUserToTicket;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('assigns a user to a ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $action = new AssignUserToTicket;

    $action($ticket, $user);

    assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => TicketUserType::Assignee->value,
    ]);
});

it('validates user access', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    // User is NOT added to project
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $action = new AssignUserToTicket;

    expect(fn () => $action($ticket, $user))
        ->toThrow(ValidationException::class);
});

it('validates user is not already assigned', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);
    $ticket->assignees()->attach($user, ['type' => TicketUserType::Assignee]);

    $action = new AssignUserToTicket;

    expect(fn () => $action($ticket, $user))
        ->toThrow(ValidationException::class);
});
