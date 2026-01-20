<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTicketsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_can_list_tickets_filtered_by_status(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // User is member of team
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Team is assigned to project
        $project->assignedTeams()->attach($team);

        // Tickets
        $ticketOpen = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Open]);
        $ticketInProgress = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::InProgress]);
        $ticketClosed = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Closed]);

        $response = $this->actingAs($user)->getJson("/api/teams/{$team->id}/tickets?status=open");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticketOpen->id);

        $responseMultiple = $this->actingAs($user)->getJson("/api/teams/{$team->id}/tickets?status[]=open&status[]=in_progress");

        $responseMultiple->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($responseMultiple->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($ticketOpen->id));
        $this->assertTrue($ids->contains($ticketInProgress->id));
        $this->assertFalse($ids->contains($ticketClosed->id));
    }

    public function test_team_member_can_list_tickets_from_project_assigned_to_team(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // User is member of team
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Team is assigned to project
        $project->assignedTeams()->attach($team);

        $ticket = Ticket::factory()->for($project)->create();

        // Another project (not assigned to team)
        $otherProject = Project::factory()->for($organization)->create();
        $otherTicket = Ticket::factory()->for($otherProject)->create();

        $response = $this->actingAs($user)->getJson("/api/teams/{$team->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticket->id);
    }

    public function test_non_team_member_cannot_list_team_tickets(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->for($organization)->create();

        // User is NOT member of team

        $response = $this->actingAs($user)->getJson("/api/teams/{$team->id}/tickets");

        $response->assertForbidden();
    }
}
