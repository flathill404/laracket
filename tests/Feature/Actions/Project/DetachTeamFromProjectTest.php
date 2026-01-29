<?php

declare(strict_types=1);

use App\Actions\Project\DetachTeamFromProject;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

describe('DetachTeamFromProject', function () {
    it('detaches a team from a project', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $organization->id]);
        $team = Team::factory()->create(['organization_id' => $organization->id]);
        $project->assignedTeams()->attach($team);

        $action = new DetachTeamFromProject;

        $action($project, $team);

        assertDatabaseMissing('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    });
});
