<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
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

describe('index', function () {
    it('lists organization members for member', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

        $otherUser = User::factory()->create();
        $organization->users()->attach($otherUser, ['role' => OrganizationRole::Member]);

        getJson("/api/organizations/{$organization->id}/members")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role'],
                ],
            ]);
    });

    it('denies access if not a member', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $organization->users()->attach($otherUser, ['role' => OrganizationRole::Owner]);

        getJson("/api/organizations/{$organization->id}/members")
            ->assertForbidden();
    });
});

describe('store', function () {
    it('invites a member to organization', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

        $newUser = User::factory()->create();

        postJson("/api/organizations/{$organization->id}/members", [
            'email' => $newUser->email,
            'role' => OrganizationRole::Member->value,
        ])
            ->assertNoContent();

        assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => $newUser->email,
            'role' => OrganizationRole::Member->value,
        ]);
    });

    it('defaults to member role if role is missing', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

        $newUser = User::factory()->create();

        postJson("/api/organizations/{$organization->id}/members", [
            'email' => $newUser->email,
        ])
            ->assertNoContent();

        assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => $newUser->email,
            'role' => OrganizationRole::Member->value,
        ]);
    });

    it('denies invite if not authorized (e.g. not owner/admin depending on policy)', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        // Attach current user as a regular member (assuming only owners/admins can invite)
        $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

        $newUser = User::factory()->create();

        postJson("/api/organizations/{$organization->id}/members", [
            'email' => $newUser->email,
            'role' => OrganizationRole::Member->value,
        ])
            ->assertForbidden();
    });
});

describe('update', function () {
    it('updates member role', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);

        patchJson("/api/organizations/{$organization->id}/members/{$member->id}", [
            'role' => OrganizationRole::Admin->value,
        ])
            ->assertNoContent();

        assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => OrganizationRole::Admin->value,
        ]);
    });

    it('denies update if not authorized', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        // Current user is just a member
        $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);

        patchJson("/api/organizations/{$organization->id}/members/{$member->id}", [
            'role' => OrganizationRole::Admin->value,
        ])
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('removes member from organization', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);

        deleteJson("/api/organizations/{$organization->id}/members/{$member->id}")
            ->assertNoContent();

        assertDatabaseMissing('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
        ]);
    });

    it('denies removal if not authorized', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        // Current user is just a member
        $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

        $member = User::factory()->create();
        $organization->users()->attach($member, ['role' => OrganizationRole::Member]);

        deleteJson("/api/organizations/{$organization->id}/members/{$member->id}")
            ->assertForbidden();
    });
});
