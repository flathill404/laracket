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
    it('adds a reviewer to the ticket', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);

        $reviewer = User::factory()->create();
        $organization->users()->attach($reviewer, ['role' => OrganizationRole::Member]);
        $project->assignedUsers()->attach($reviewer);

        postJson("/api/tickets/{$ticket->id}/reviewers", [
            'user_id' => $reviewer->id,
        ])
            ->assertNoContent();

        assertDatabaseHas('ticket_user', [
            'ticket_id' => $ticket->id,
            'user_id' => $reviewer->id,
            'type' => TicketUserType::Reviewer->value,
        ]);
    });

    it('denies adding reviewer if not authorized', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);
        // User is not member

        $reviewer = User::factory()->create();

        postJson("/api/tickets/{$ticket->id}/reviewers", [
            'user_id' => $reviewer->id,
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

        postJson("/api/tickets/{$ticket->id}/reviewers", [])
            ->assertNotFound();
    });
});

describe('destroy', function () {
    it('removes a reviewer from the ticket', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);

        $reviewer = User::factory()->create();
        $ticket->reviewers()->attach($reviewer, ['type' => TicketUserType::Reviewer]);

        deleteJson("/api/tickets/{$ticket->id}/reviewers/{$reviewer->id}")
            ->assertNoContent();

        assertDatabaseMissing('ticket_user', [
            'ticket_id' => $ticket->id,
            'user_id' => $reviewer->id,
            'type' => TicketUserType::Reviewer->value,
        ]);
    });

    it('denies removing reviewer if not authorized', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
        ]);
        // User not authorized

        $reviewer = User::factory()->create();
        $ticket->reviewers()->attach($reviewer, ['type' => TicketUserType::Reviewer]);

        deleteJson("/api/tickets/{$ticket->id}/reviewers/{$reviewer->id}")
            ->assertForbidden();
    });
});
