<?php

use App\Actions\Project\AttachTeamToProject;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('attaches a team to a project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $team = Team::factory()->create(['organization_id' => $organization->id]);

    $action = new AttachTeamToProject;

    $action($project, $team);

    assertDatabaseHas('project_team', [
        'project_id' => $project->id,
        'team_id' => $team->id,
    ]);
});

it('validates team belongs to organization', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $otherOrganization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $otherOrganization->id]);

    $action = new AttachTeamToProject;

    expect(fn() => $action($project, $team))
        ->toThrow(ValidationException::class);
});

it('validates team is not already attached', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $project->assignedTeams()->attach($team);

    $action = new AttachTeamToProject;

    expect(fn() => $action($project, $team))
        ->toThrow(ValidationException::class);
});
