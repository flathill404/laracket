<?php

namespace Tests\Feature\Actions\Project;

use App\Actions\Project\UpdateProject;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_project(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $action = new UpdateProject;

        $input = [
            'name' => 'UpdatedProjectName',
            'assigned_users' => [$user->id],
            'assigned_teams' => [$team->id],
        ];

        $action->update($project, $input);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'UpdatedProjectName',
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

    public function test_it_validates_project_update(): void
    {
        $project = Project::factory()->create();
        $action = new UpdateProject;

        $this->assertThrows(function () use ($action, $project) {
            $action->update($project, [
                'name' => 'Invalid Name!',
            ]);
        }, \Illuminate\Validation\ValidationException::class);

        $this->assertThrows(function () use ($action, $project) {
            $action->update($project, [
                'display_name' => str_repeat('a', 51),
            ]);
        }, \Illuminate\Validation\ValidationException::class);
    }
}
