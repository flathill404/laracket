<?php

declare(strict_types=1);

use App\Actions\Ticket\UnassignUserFromTicket;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

it('unassigns a user from a ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);
    $ticket->assignees()->attach($user, ['type' => TicketUserType::Assignee]);

    $action = new UnassignUserFromTicket;

    $action($ticket, $user);

    assertDatabaseMissing('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => TicketUserType::Assignee->value,
    ]);
});
