<?php

namespace Tests\Feature\Api;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTicketsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_tickets_filtered_by_status(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // Assign user to project
        $project->assignedUsers()->attach($user);

        // Tickets
        $ticketOpen = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Open]);
        $ticketInProgress = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::InProgress]);
        $ticketClosed = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Closed]);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets?status=open");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticketOpen->id);

        $responseMultiple = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets?status[]=open&status[]=in_progress");

        $responseMultiple->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($responseMultiple->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($ticketOpen->id));
        $this->assertTrue($ids->contains($ticketInProgress->id));
        $this->assertFalse($ids->contains($ticketClosed->id));
    }

    public function test_user_can_list_assigned_project_tickets(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // Assign user to project
        $project->assignedUsers()->attach($user);

        // Tickets in the project
        $ticket1 = Ticket::factory()->for($project)->create();
        $ticket2 = Ticket::factory()->for($project)->create();

        // Another project ticket (not assigned)
        $otherProject = Project::factory()->for($organization)->create();
        $otherTicket = Ticket::factory()->for($otherProject)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($ticket1->id));
        $this->assertTrue($ids->contains($ticket2->id));
        $this->assertFalse($ids->contains($otherTicket->id));
    }

    public function test_user_can_list_team_assigned_project_tickets(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // Assign user to team
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Assign team to project
        $project->assignedTeams()->attach($team);

        $ticket = Ticket::factory()->for($project)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticket->id);
    }

    public function test_organization_owner_can_list_all_tickets_in_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $project = Project::factory()->for($organization)->create();

        $ticket = Ticket::factory()->for($project)->create();

        // Another organization ticket
        $otherOrg = Organization::factory()->create();
        $otherProject = Project::factory()->for($otherOrg)->create();
        $otherTicket = Ticket::factory()->for($otherProject)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticket->id);
    }

    public function test_organization_admin_can_list_all_tickets_in_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->for($organization)->create();
        $ticket = Ticket::factory()->for($project)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ticket->id);
    }

    public function test_cannot_view_other_users_tickets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$otherUser->id}/tickets");

        $response->assertForbidden();
    }
}
