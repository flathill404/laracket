<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_joined_teams(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->for($organization)->create();

        // Assign user to team
        $team->users()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Another team where user is not member
        $otherTeam = Team::factory()->for($organization)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/teams");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $team->id);
    }

    public function test_organization_owner_can_list_all_teams_in_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $team1 = Team::factory()->for($organization)->create();
        $team2 = Team::factory()->for($organization)->create();

        // Another organization team
        $otherOrg = Organization::factory()->create();
        $otherTeam = Team::factory()->for($otherOrg)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/teams");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_organization_admin_can_list_all_teams_in_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        
        // Make user admin
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Admin]);

        $team1 = Team::factory()->for($organization)->create();
        $team2 = Team::factory()->for($organization)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/teams");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_unrelated_teams(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->for($organization)->create();

        // User is just a member of org but not in team
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Member]);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/teams");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_cannot_view_other_users_teams(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$otherUser->id}/teams");

        $response->assertForbidden();
    }
}
