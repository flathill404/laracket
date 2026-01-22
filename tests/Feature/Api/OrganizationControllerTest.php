<?php

declare(strict_types=1);

use App\Models\Organization;
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

describe('index', function () {
    it('lists organizations for authenticated user', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organizations = Organization::factory(3)->create([
            'owner_user_id' => $user->id,
        ]);
        $organizations->each(fn ($org) => $org->users()->attach($user));

        $otherUser = User::factory()->create();
        $otherOrganization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $otherOrganization->users()->attach($otherUser);

        getJson('/api/organizations')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name'],
                ],
            ]);
    });

    it('returns empty list if user has no organizations', function () {
        $user = User::factory()->create();
        actingAs($user);

        getJson('/api/organizations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('requires authentication', function () {
        // No actingAs needed here as we test unauthorized, but the instruction is to remove beforeEach which had actingAs.
        // If we don't call actingAs, we are guests.
        // But we need to make sure we don't regress. The original code acted as user globally, then logout.
        // 'auth()->logout();' implies user was logged in.
        // So we should setup user and logout? Or just not actAs?
        // If we actAs($user), then auth()->logout(), it tests logout.
        // If we just don't actAs, it tests guest access.
        // The test explicitly calls auth()->logout().
        $user = User::factory()->create();
        actingAs($user);
        auth()->logout();
        getJson('/api/organizations')
            ->assertUnauthorized();
    });
});

describe('store', function () {
    it('creates an organization', function () {
        $user = User::factory()->create();
        actingAs($user);

        $data = [
            'name' => 'test-org',
            'display_name' => 'Test Organization',
        ];

        postJson('/api/organizations', $data)
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'test-org',
                'display_name' => 'Test Organization',
                'owner_user_id' => $user->id,
            ]);

        assertDatabaseHas('organizations', [
            'name' => 'test-org',
            'owner_user_id' => $user->id,
        ]);

        assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    });

    it('validates input', function () {
        $user = User::factory()->create();
        actingAs($user);

        postJson('/api/organizations', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('show', function () {
    it('shows organization details', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user);

        getJson("/api/organizations/{$organization->id}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
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
        // Do not attach user to organization

        getJson("/api/organizations/{$organization->id}")
            ->assertForbidden();
    });
});

describe('update', function () {
    it('updates organization', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user);

        $data = [
            'name' => 'updated-org',
            'display_name' => 'Updated Organization',
        ];

        putJson("/api/organizations/{$organization->id}", $data)
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'updated-org',
                'display_name' => 'Updated Organization',
            ]);

        assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'updated-org',
        ]);
    });

    it('denies update if not owner', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $organization->users()->attach($user); // Member but not owner (assuming policy checks owner or specific permission)

        $data = [
            'name' => 'updated-org',
        ];

        putJson("/api/organizations/{$organization->id}", $data)
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('deletes organization', function () {
        $user = User::factory()->create();
        actingAs($user);

        $organization = Organization::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $organization->users()->attach($user);

        deleteJson("/api/organizations/{$organization->id}")
            ->assertNoContent();

        assertDatabaseMissing('organizations', [
            'id' => $organization->id,
        ]);
    });

    it('denies delete if not owner', function () {
        $user = User::factory()->create();
        actingAs($user);

        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $organization->users()->attach($user); // Member but not owner

        deleteJson("/api/organizations/{$organization->id}")
            ->assertForbidden();
    });
});
