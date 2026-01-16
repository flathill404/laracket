<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_assigned_projects(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // Assign user to project
        $project->assignedUsers()->attach($user);

        // Another project not assigned
        $otherProject = Project::factory()->for($organization)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $project->id);
    }

    public function test_user_can_list_team_assigned_projects(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // Assign user to team
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Assign team to project
        $project->assignedTeams()->attach($team);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $project->id);
    }

    public function test_organization_owner_can_list_all_projects_in_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $project1 = Project::factory()->for($organization)->create();
        $project2 = Project::factory()->for($organization)->create();

        // Another organization project
        $otherOrg = Organization::factory()->create();
        $otherProject = Project::factory()->for($otherOrg)->create();

        $this->actingAs($user);

        $response = $this->getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
            
        // Assert contains both IDs
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($project1->id));
        $this->assertTrue($ids->contains($project2->id));
        $this->assertFalse($ids->contains($otherProject->id));
    }

    public function test_organization_admin_can_list_all_projects_in_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        
        // Make user admin
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Admin]);

        $project1 = Project::factory()->for($organization)->create();
        $project2 = Project::factory()->for($organization)->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_unrelated_projects(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // User is just a member of org but not assigned to project
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Member]);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_cannot_view_other_users_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$otherUser->id}/projects");

        $response->assertForbidden();
    }
}
