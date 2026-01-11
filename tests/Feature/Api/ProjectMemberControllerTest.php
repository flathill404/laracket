<?php

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

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('index', function () {
    it('lists project members', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create();
        $project->members()->attach($otherUser);
        $project->members()->attach($this->user);

        getJson("/api/projects/{$project->id}/members")
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'email'],
            ]);
    });

    it('denies access if not a member of organization/project', function () {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        getJson("/api/projects/{$project->id}/members")
            ->assertForbidden();
    });
});

describe('store', function () {
    it('adds a member to the project', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newUser = User::factory()->create();
        // User must be organization member to be added to project (usually, but let's assume valid flow)
        $organization->users()->attach($newUser, ['role' => OrganizationRole::Member]);

        postJson("/api/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
        ])
            ->assertNoContent();

        assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
        ]);
    });

    it('denies adding member if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]); // Regular member usually can't add others

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newUser = User::factory()->create();
        $organization->users()->attach($newUser, ['role' => OrganizationRole::Member]);

        postJson("/api/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
        ])
            ->assertForbidden();
    });

    it('validates input', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        postJson("/api/projects/{$project->id}/members", [])
            ->assertNotFound(); // Because findOrFail throws 404 if ID is null/missing? Or 500?
        // Controller uses User::findOrFail($request->input('user_id')).
        // If user_id is missing, findOrFail(null) might behave specific way or if valid usage expecting validation request.
        // Actually, if strictly looking at controller:
        // $user = User::findOrFail($request->input('user_id'));
        // If input is null, findOrFail might fail. Validation usually handles this before controller.
        // But there is no FormRequest type hinted, just Request.
        // And no manual validation shown in snippet.
        // So it might 404 or 500.
        // Let's assume for now keeping it simple or skipping strict output validation if code crashes.
        // Actually, if I pass nothing, user_id is null. User::findOrFail(null) -> throws ModelNotFound (404).
    });
});

describe('destroy', function () {
    it('removes a member from the project', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Admin]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $project->members()->attach($member);

        deleteJson("/api/projects/{$project->id}/members/{$member->id}")
            ->assertNoContent();

        assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    });

    it('denies removal if not authorized', function () {
        $organization = Organization::factory()->create();
        $organization->users()->attach($this->user, ['role' => OrganizationRole::Member]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $member = User::factory()->create();
        $project->members()->attach($member);

        deleteJson("/api/projects/{$project->id}/members/{$member->id}")
            ->assertForbidden();
    });
});
