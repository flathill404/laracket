<?php

namespace Tests\Feature\Actions\Project;

use App\Actions\Project\CreateProject;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_project(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->create(['organization_id' => $organization->id]); // Ensure team belongs to same org usually, but for test logic mainly check DB
        $action = new CreateProject;

        $data = [
            'name' => 'Test Project',
            'display_name' => 'Test Project Display',
            'description' => 'Test Description',
            'assigned_users' => [$user->id],
            'assigned_teams' => [$team->id],
        ];

        $project = $action->create($user, $organization, $data);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Test Project',
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }
}
