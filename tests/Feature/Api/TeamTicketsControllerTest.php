<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('index', function () {
    it('allows team member to list tickets filtered by status', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // User is member of organization and team
        $organization->users()->attach($user);
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Team is assigned to project
        $project->assignedTeams()->attach($team);

        // Tickets
        $ticketOpen = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Open]);
        $ticketInProgress = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::InProgress]);
        $ticketClosed = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Closed]);

        actingAs($user);

        $response = getJson("/api/teams/{$team->id}/tickets?status=open");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticketOpen->id);

        $responseMultiple = getJson("/api/teams/{$team->id}/tickets?status[]=open&status[]=in_progress");

        $responseMultiple->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($responseMultiple->json('data'))->pluck('id');
        expect($ids->contains($ticketOpen->id))->toBeTrue();
        expect($ids->contains($ticketInProgress->id))->toBeTrue();
        expect($ids->contains($ticketClosed->id))->toBeFalse();
    });

    it('allows team member to list tickets from project assigned to team', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // User is member of organization and team
        $organization->users()->attach($user);
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Team is assigned to project
        $project->assignedTeams()->attach($team);

        $ticket = Ticket::factory()->for($project)->create();

        // Another project (not assigned to team)
        $otherProject = Project::factory()->for($organization)->create();
        $otherTicket = Ticket::factory()->for($otherProject)->create();

        actingAs($user);

        $response = getJson("/api/teams/{$team->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticket->id);
    });

    it('denies non team member from listing team tickets', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->for($organization)->create();

        // User is NOT member of team

        actingAs($user);

        $response = getJson("/api/teams/{$team->id}/tickets");

        $response->assertForbidden();
    });
});
