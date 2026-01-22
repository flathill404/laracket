<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

describe('store', function () {
    it('attaches a team to the project', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        postJson("/api/projects/{$project->id}/teams", [
            'team_id' => $team->id,
        ])
            ->assertNoContent();

        assertDatabaseHas('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    });

    it('denies attaching team if not authorized', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        postJson("/api/projects/{$project->id}/teams", [
            'team_id' => $team->id,
        ])
            ->assertForbidden();
    });

    it('validates input', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        postJson("/api/projects/{$project->id}/teams", [])
            ->assertNotFound();
    });
});

describe('destroy', function () {
    it('detaches a team from the project', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $project->assignedTeams()->attach($team);

        deleteJson("/api/projects/{$project->id}/teams/{$team->id}")
            ->assertNoContent();

        assertDatabaseMissing('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    });

    it('denies detaching team if not authorized', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $project->assignedTeams()->attach($team);

        deleteJson("/api/projects/{$project->id}/teams/{$team->id}")
            ->assertForbidden();
    });
});
