<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('index', function () {
    it('lists team members', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create();
        $organization->users()->attach($otherUser, ['role' => OrganizationRole::Member]);

        $team->users()->attach($this->user, ['role' => TeamRole::Leader]);
        $team->users()->attach($otherUser, ['role' => TeamRole::Member]);

        getJson("/api/teams/{$team->id}/members")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
            ]);
    });

    it('denies access if not a member of organization', function () {
        $organization = Organization::factory()->create();
        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        getJson("/api/teams/{$team->id}/members")
            ->assertForbidden();
    });
});

describe('store', function () {
    it('adds a member to the team', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newUser = User::factory()->create();
        $organization->users()->attach($newUser, ['role' => OrganizationRole::Member]);

        postJson("/api/teams/{$team->id}/members", [
            'user_id' => $newUser->id,
        ])
            ->assertNoContent();

        assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newUser->id,
            'role' => TeamRole::Member->value,
        ]);
    });

    it('denies adding member if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newUser = User::factory()->create();
        $organization->users()->attach($newUser, ['role' => OrganizationRole::Member]);

        postJson("/api/teams/{$team->id}/members", [
            'user_id' => $newUser->id,
        ])
            ->assertForbidden();
    });

    it('validates user belongs to organization', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newUser = User::factory()->create();
        // Not adding to organization

        postJson("/api/teams/{$team->id}/members", [
            'user_id' => $newUser->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user']);
    });
});

describe('update', function () {
    it('updates member role', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);
        $team->users()->attach($member, ['role' => TeamRole::Member]);

        patchJson("/api/teams/{$team->id}/members/{$member->id}", [
            'role' => TeamRole::Leader->value,
        ])
            ->assertNoContent();

        assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => TeamRole::Leader->value,
        ]);
    });

    it('denies update if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);
        $team->users()->attach($member, ['role' => TeamRole::Member]);

        patchJson("/api/teams/{$team->id}/members/{$member->id}", [
            'role' => TeamRole::Leader->value,
        ])
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('removes a member from the team', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);
        $team->users()->attach($member, ['role' => TeamRole::Member]);

        deleteJson("/api/teams/{$team->id}/members/{$member->id}")
            ->assertNoContent();

        assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    });

    it('denies removal if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $team = Team::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);
        $team->users()->attach($member, ['role' => TeamRole::Member]);

        deleteJson("/api/teams/{$team->id}/members/{$member->id}")
            ->assertForbidden();
    });
});
