<?php

use App\Enums\OrganizationRole;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('store', function () {
    it('assigns a user to the ticket', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);

        $assignee = User::factory()->create();
        $organization->users()->attach($assignee, ['role' => OrganizationRole::Member]);
        $project->assignedUsers()->attach($assignee);

        postJson("/api/tickets/{$ticket->id}/assignees", [
            'user_id' => $assignee->id,
        ])
            ->assertNoContent();

        assertDatabaseHas('ticket_user', [
            'ticket_id' => $ticket->id,
            'user_id' => $assignee->id,
            'type' => TicketUserType::Assignee->value,
        ]);
    });

    it('denies assigning user if not authorized', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);
        // User is not member

        $assignee = User::factory()->create();

        postJson("/api/tickets/{$ticket->id}/assignees", [
            'user_id' => $assignee->id,
        ])
            ->assertForbidden();
    });

    it('validates input', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);

        postJson("/api/tickets/{$ticket->id}/assignees", [])
            ->assertNotFound();
    });
});

describe('destroy', function () {
    it('unassigns a user from the ticket', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);

        $assignee = User::factory()->create();
        $ticket->assignees()->attach($assignee, ['type' => TicketUserType::Assignee]);

        deleteJson("/api/tickets/{$ticket->id}/assignees/{$assignee->id}")
            ->assertNoContent();

        assertDatabaseMissing('ticket_user', [
            'ticket_id' => $ticket->id,
            'user_id' => $assignee->id,
            'type' => TicketUserType::Assignee->value,
        ]);
    });

    it('denies unassigning user if not authorized', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);
        // User not authorized

        $assignee = User::factory()->create();
        $ticket->assignees()->attach($assignee, ['type' => TicketUserType::Assignee]);

        deleteJson("/api/tickets/{$ticket->id}/assignees/{$assignee->id}")
            ->assertForbidden();
    });
});
