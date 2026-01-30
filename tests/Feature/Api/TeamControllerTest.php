<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(LazilyRefreshDatabase::class);

describe('TeamController', function () {
    describe('index', function () {
        it('lists teams for organization member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $teams = Team::factory(3)->create([
                'organization_id' => $organization->id,
            ]);

            getJson("/api/organizations/{$organization->id}/teams")
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'slug', 'name', 'organization_id'],
                    ],
                ]);
        });

        it('denies access if not a member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            // User is not a member

            getJson("/api/organizations/{$organization->id}/teams")
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('creates a team', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $data = [
                'slug' => 'test-team',
                'name' => 'Test Team',
                'description' => 'Test Description',
            ];

            postJson("/api/organizations/{$organization->id}/teams", $data)
                ->assertCreated()
                ->assertJsonFragment([
                    'slug' => 'test-team',
                    'name' => 'Test Team',
                    'organization_id' => $organization->id,
                ]);

            assertDatabaseHas('teams', [
                'slug' => 'test-team',
                'organization_id' => $organization->id,
            ]);
        });

        it('denies creation if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $data = [
                'slug' => 'test-team',
                'name' => 'Test Team',
            ];

            postJson("/api/organizations/{$organization->id}/teams", $data)
                ->assertForbidden();
        });

        it('validates input', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            postJson("/api/organizations/{$organization->id}/teams", [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug', 'name']);
        });
    });

    describe('show', function () {
        it('shows team details', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);

            getJson("/api/organizations/{$organization->id}/teams/{$team->id}")
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $team->id,
                        'slug' => $team->slug,
                        'name' => $team->name,
                    ],
                ]);
        });

        it('denies access if not a member of organization', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);
            // User not attached

            getJson("/api/organizations/{$organization->id}/teams/{$team->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('updates team', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);

            $data = [
                'slug' => 'updated-team',
                'name' => 'Updated Team',
            ];

            putJson("/api/organizations/{$organization->id}/teams/{$team->id}", $data)
                ->assertOk()
                ->assertJsonFragment([
                    'slug' => 'updated-team',
                    'name' => 'Updated Team',
                ]);

            assertDatabaseHas('teams', [
                'id' => $team->id,
                'slug' => 'updated-team',
            ]);
        });

        it('denies update if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);

            putJson("/api/organizations/{$organization->id}/teams/{$team->id}", ['slug' => 'new-name'])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes team', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);

            deleteJson("/api/organizations/{$organization->id}/teams/{$team->id}")
                ->assertNoContent();

            assertDatabaseMissing('teams', [
                'id' => $team->id,
            ]);
        });

        it('denies delete if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $team = Team::factory()->create([
                'organization_id' => $organization->id,
            ]);

            deleteJson("/api/organizations/{$organization->id}/teams/{$team->id}")
                ->assertForbidden();
        });
    });
});
