<?php

declare(strict_types=1);

use App\Actions\Project\CreateProject;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

describe('CreateProject', function () {
    it('creates a project', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $team = Team::factory()->create(['organization_id' => $organization->id]);
        $action = new CreateProject;

        $input = [
            'slug' => 'test-project',
            'key' => 'TEST',
            'name' => 'Test Project Display',
            'description' => 'Test Description',
            'assigned_users' => [$user->id],
            'assigned_teams' => [$team->id],
        ];

        $project = $action($user, $organization, $input);

        assertDatabaseHas('projects', [
            'id' => $project->id,
            'slug' => 'test-project',
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

        expect(fn () => $action($user, $organization, [
            'slug' => 'invalid-name!',
            'key' => 'TEST',
            'name' => 'Valid Display',
        ]))->toThrow(ValidationException::class);

        expect(fn () => $action($user, $organization, [
            'slug' => 'valid-name',
            'key' => 'TEST',
            'name' => str_repeat('a', 51),
        ]))->toThrow(ValidationException::class);
    });
});
