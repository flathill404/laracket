<?php

use App\Actions\Project\CreateProject;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('creates a project', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $action = new CreateProject;

    $input = [
        'name' => 'TestProject',
        'display_name' => 'Test Project Display',
        'description' => 'Test Description',
        'assigned_users' => [$user->id],
        'assigned_teams' => [$team->id],
    ];

    $project = $action->create($user, $organization, $input);

    assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'TestProject',
        'organization_id' => $organization->id,
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

it('validates project creation', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $action = new CreateProject;

    expect(fn () => $action->create($user, $organization, [
        'name' => 'Invalid Name!',
        'display_name' => 'Valid Display',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action->create($user, $organization, [
        'name' => 'valid-name',
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
