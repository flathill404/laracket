<?php

use App\Actions\Ticket\AddReviewerToTicket;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('adds a reviewer to a ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $action = new AddReviewerToTicket;

    $action($ticket, $user);

    assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => TicketUserType::Reviewer->value,
    ]);
});

it('validates user access', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    // User is NOT added to project
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $action = new AddReviewerToTicket;

    expect(fn () => $action($ticket, $user))
        ->toThrow(ValidationException::class);
});

it('validates user is not already a reviewer', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);
    $ticket->reviewers()->attach($user, ['type' => TicketUserType::Reviewer]);

    $action = new AddReviewerToTicket;

    expect(fn () => $action($ticket, $user))
        ->toThrow(ValidationException::class);
});
