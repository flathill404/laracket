<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
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

describe('ProjectController', function () {
    describe('index', function () {
        it('lists projects for organization member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $projects = Project::factory(3)->create([
                'organization_id' => $organization->id,
            ]);

            getJson("/api/organizations/{$organization->id}/projects")
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'display_name', 'organization_id'],
                    ],
                ]);
        });

        it('denies access if not a member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            // User is not a member

            getJson("/api/organizations/{$organization->id}/projects")
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('creates a project', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $data = [
                'name' => 'test-project',
                'display_name' => 'Test Project',
                'description' => 'Test Description',
            ];

            postJson("/api/organizations/{$organization->id}/projects", $data)
                ->assertCreated()
                ->assertJsonFragment([
                    'name' => 'test-project',
                    'display_name' => 'Test Project',
                    'organization_id' => $organization->id,
                ]);

            assertDatabaseHas('projects', [
                'name' => 'test-project',
                'organization_id' => $organization->id,
            ]);

            // Verify default member (creator) might be added if logic exists, but primary check is project creation
        });

        it('validates input', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            postJson("/api/organizations/{$organization->id}/projects", [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });
    });

    describe('show', function () {
        it('shows project details', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            getJson("/api/projects/{$project->id}")
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $project->id,
                        'name' => $project->name,
                    ],
                ]);
        });

        it('denies access if not a member of organization', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            // User not attached

            getJson("/api/projects/{$project->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('updates project', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            $data = [
                'name' => 'updated-project',
                'display_name' => 'Updated Project',
            ];

            putJson("/api/projects/{$project->id}", $data)
                ->assertOk()
                ->assertJsonFragment([
                    'name' => 'updated-project',
                    'display_name' => 'Updated Project',
                ]);

            assertDatabaseHas('projects', [
                'id' => $project->id,
                'name' => 'updated-project',
            ]);
        });

        it('denies update if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $otherUser = User::factory()->create();
            $organization = Organization::factory()->create(['owner_user_id' => $otherUser->id]);
            // User is not member (or minimal member role check depending on policy)

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            putJson("/api/projects/{$project->id}", ['name' => 'new-name'])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes project', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create([
                'owner_user_id' => $user->id,
            ]);
            $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            deleteJson("/api/projects/{$project->id}")
                ->assertNoContent();

            // Project model does not use SoftDeletes based on previous view_file
            // But check if it uses it. Wait, I viewed Project.php and it has `use HasUuids;` but NOT `use SoftDeletes;`.
            // So I should use assertDatabaseMissing.
            assertDatabaseMissing('projects', [
                'id' => $project->id,
            ]);
        });

        it('denies delete if not owner', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            deleteJson("/api/projects/{$project->id}")
                ->assertForbidden();
        });
    });
});
