<?php

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

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('index', function () {
    it('lists organizations for authenticated user', function () {
        $organizations = Organization::factory(3)->create([
            'owner_user_id' => $this->user->id,
        ]);
        $organizations->each(fn ($org) => $org->users()->attach($this->user));

        $otherUser = User::factory()->create();
        $otherOrganization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $otherOrganization->users()->attach($otherUser);

        getJson('/api/organizations')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'display_name'],
            ]);
    });

    it('returns empty list if user has no organizations', function () {
        getJson('/api/organizations')
            ->assertOk()
            ->assertJsonCount(0);
    });

    it('requires authentication', function () {
        auth()->logout();
        getJson('/api/organizations')
            ->assertUnauthorized();
    });
});

describe('store', function () {
    it('creates an organization', function () {
        $data = [
            'name' => 'test-org',
            'display_name' => 'Test Organization',
        ];

        postJson('/api/organizations', $data)
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'test-org',
                'display_name' => 'Test Organization',
                'owner_user_id' => $this->user->id,
            ]);

        assertDatabaseHas('organizations', [
            'name' => 'test-org',
            'owner_user_id' => $this->user->id,
        ]);

        assertDatabaseHas('organization_user', [
            'user_id' => $this->user->id,
            'role' => 'admin',
        ]);
    });

    it('validates input', function () {
        postJson('/api/organizations', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('show', function () {
    it('shows organization details', function () {
        $organization = Organization::factory()->create([
            'owner_user_id' => $this->user->id,
        ]);
        $organization->users()->attach($this->user);

        getJson("/api/organizations/{$organization->id}")
            ->assertOk()
            ->assertJson([
                'id' => $organization->id,
                'name' => $organization->name,
            ]);
    });

    it('denies access if not a member', function () {
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
        $organization = Organization::factory()->create([
            'owner_user_id' => $this->user->id,
        ]);
        $organization->users()->attach($this->user);

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
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $organization->users()->attach($this->user); // Member but not owner (assuming policy checks owner or specific permission)

        $data = [
            'name' => 'updated-org',
        ];

        putJson("/api/organizations/{$organization->id}", $data)
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('deletes organization', function () {
        $organization = Organization::factory()->create([
            'owner_user_id' => $this->user->id,
        ]);
        $organization->users()->attach($this->user);

        deleteJson("/api/organizations/{$organization->id}")
            ->assertNoContent();

        assertDatabaseMissing('organizations', [
            'id' => $organization->id,
        ]);
    });

    it('denies delete if not owner', function () {
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_user_id' => $otherUser->id,
        ]);
        $organization->users()->attach($this->user); // Member but not owner

        deleteJson("/api/organizations/{$organization->id}")
            ->assertForbidden();
    });
});
