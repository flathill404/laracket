<?php

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

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('index', function () {
    it('lists teams for organization member', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $teams = Team::factory(3)->create([
            'organization_id' => $organization->id,
        ]);

        getJson("/api/organizations/{$organization->id}/teams")
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'display_name', 'organization_id'],
            ]);
    });

    it('denies access if not a member', function () {
        $organization = Organization::factory()->create();
        // User is not a member

        getJson("/api/organizations/{$organization->id}/teams")
            ->assertForbidden();
    });
});

describe('store', function () {
    it('creates a team', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $data = [
            'name' => 'test-team',
            'display_name' => 'Test Team',
            'description' => 'Test Description',
        ];

        postJson("/api/organizations/{$organization->id}/teams", $data)
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'test-team',
                'display_name' => 'Test Team',
                'organization_id' => $organization->id,
            ]);

        assertDatabaseHas('teams', [
            'name' => 'test-team',
            'organization_id' => $organization->id,
        ]);
    });

    it('denies creation if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $data = [
            'name' => 'test-team',
            'display_name' => 'Test Team',
        ];

        postJson("/api/organizations/{$organization->id}/teams", $data)
            ->assertForbidden();
    });

    it('validates input', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        postJson("/api/organizations/{$organization->id}/teams", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('show', function () {
    it('shows team details', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        getJson("/api/teams/{$team->id}")
            ->assertOk()
            ->assertJson([
                'id' => $team->id,
                'name' => $team->name,
            ]);
    });

    it('denies access if not a member of organization', function () {
        $organization = Organization::factory()->create();
        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);
        // User not attached

        getJson("/api/teams/{$team->id}")
            ->assertForbidden();
    });
});

describe('update', function () {
    it('updates team', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $data = [
            'name' => 'updated-team',
            'display_name' => 'Updated Team',
        ];

        putJson("/api/teams/{$team->id}", $data)
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'updated-team',
                'display_name' => 'Updated Team',
            ]);

        assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'updated-team',
        ]);
    });

    it('denies update if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        putJson("/api/teams/{$team->id}", ['name' => 'new-name'])
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('deletes team', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        deleteJson("/api/teams/{$team->id}")
            ->assertNoContent();

        assertDatabaseMissing('teams', [
            'id' => $team->id,
        ]);
    });

    it('denies delete if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        deleteJson("/api/teams/{$team->id}")
            ->assertForbidden();
    });
});
