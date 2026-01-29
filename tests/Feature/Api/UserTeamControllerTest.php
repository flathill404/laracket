<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('UserTeamController', function () {
    describe('index', function () {
        it('allows user to list joined teams', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $team = Team::factory()->for($organization)->create();

            // Assign user to team
            $team->users()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

            // Another team where user is not member
            $otherTeam = Team::factory()->for($organization)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/teams");

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $team->id);
        });

        it('allows organization owner to list all teams in organization', function () {
            $user = User::factory()->create();
            $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
            $team1 = Team::factory()->for($organization)->create();
            $team2 = Team::factory()->for($organization)->create();

            // Another organization team
            $otherOrg = Organization::factory()->create();
            $otherTeam = Team::factory()->for($otherOrg)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/teams");

            $response->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('allows organization admin to list all teams in organization', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();

            // Make user admin
            $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Admin]);

            $team1 = Team::factory()->for($organization)->create();
            $team2 = Team::factory()->for($organization)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/teams");

            $response->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('denies user from seeing unrelated teams', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $team = Team::factory()->for($organization)->create();

            // User is just a member of org but not in team
            $organization->users()->attach($user, ['role' => \App\Enums\OrganizationRole::Member]);

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/teams");

            $response->assertOk()
                ->assertJsonCount(0, 'data');
        });

        it('denies viewing other users teams', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();

            actingAs($user);

            $response = getJson("/api/users/{$otherUser->id}/teams");

            $response->assertForbidden();
        });
    });
});
