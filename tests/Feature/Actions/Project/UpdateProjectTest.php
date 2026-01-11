<?php

use App\Actions\Project\UpdateProject;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('updates a project', function () {
    $project = Project::factory()->create();
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $action = new UpdateProject;

    $input = [
        'name' => 'UpdatedProjectName',
        'assigned_users' => [$user->id],
        'assigned_teams' => [$team->id],
    ];

    $action($project, $input);

    assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'UpdatedProjectName',
    ]);

    assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    assertDatabaseHas('project_team', [
        'project_id' => $project->id,
        'team_id' => $team->id,
    ]);
});

it('validates project update', function () {
    $project = Project::factory()->create();
    $action = new UpdateProject;

    expect(fn() => $action($project, [
        'name' => 'Invalid Name!',
    ]))->toThrow(ValidationException::class);

    expect(fn() => $action($project, [
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
