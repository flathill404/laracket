<?php

use App\Actions\Ticket\RemoveReviewerFromTicket;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

it('removes a reviewer from a ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);
    $ticket->reviewers()->attach($user, ['type' => TicketUserType::Reviewer]);

    $action = new RemoveReviewerFromTicket;

    $action($ticket, $user);

    assertDatabaseMissing('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => TicketUserType::Reviewer->value,
    ]);
});
