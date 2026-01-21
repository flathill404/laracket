<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('index', function () {
    it('allows user to list assigned projects', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // Assign user to project
        $project->assignedUsers()->attach($user);

        // Another project not assigned
        $otherProject = Project::factory()->for($organization)->create();

        actingAs($user);

        $response = getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $project->id);
    });

    it('allows user to list team assigned projects', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $team = Team::factory()->for($organization)->create();

        // Assign user to team
        $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

        // Assign team to project
        $project->assignedTeams()->attach($team);

        actingAs($user);

        $response = getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $project->id);
    });

    it('allows organization owner to list all projects in organization', function () {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $project1 = Project::factory()->for($organization)->create();
        $project2 = Project::factory()->for($organization)->create();

        // Another organization project
        $otherOrg = Organization::factory()->create();
        $otherProject = Project::factory()->for($otherOrg)->create();

        actingAs($user);

        $response = getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        // Assert contains both IDs
        $ids = collect($response->json('data'))->pluck('id');
        expect($ids->contains($project1->id))->toBeTrue();
        expect($ids->contains($project2->id))->toBeTrue();
        expect($ids->contains($otherProject->id))->toBeFalse();
    });

    it('allows organization admin to list all projects in organization', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();

        // Make user admin
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Admin]);

        $project1 = Project::factory()->for($organization)->create();
        $project2 = Project::factory()->for($organization)->create();

        actingAs($user);

        $response = getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('denies user from seeing unrelated projects', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->for($organization)->create();

        // User is just a member of org but not assigned to project
        $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Member]);

        actingAs($user);

        $response = getJson("/api/users/{$user->id}/projects");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('denies viewing other users projects', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        actingAs($user);

        $response = getJson("/api/users/{$otherUser->id}/projects");

        $response->assertForbidden();
    });
});
