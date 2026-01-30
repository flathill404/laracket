<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('slugs must be unique within an organization', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_user_id' => $user->id]);

    // Create first project
    Project::factory()->create([
        'organization_id' => $org->id,
        'slug' => 'my-project',
        'name' => 'My Project',
    ]);

    // Attempt to create second project with same slug in same org
    expect(fn () => Project::factory()->create([
        'organization_id' => $org->id,
        'slug' => 'my-project',
        'name' => 'Duplicate Project',
    ]))->toThrow(QueryException::class);
});

test('slugs can be duplicated across different organizations', function () {
    $user = User::factory()->create();
    $org1 = Organization::factory()->create(['owner_user_id' => $user->id]);
    $org2 = Organization::factory()->create(['owner_user_id' => $user->id]);

    $p1 = Project::factory()->create([
        'organization_id' => $org1->id,
        'slug' => 'common-slug',
        'name' => 'Project 1',
    ]);

    $p2 = Project::factory()->create([
        'organization_id' => $org2->id,
        'slug' => 'common-slug',
        'name' => 'Project 2',
    ]);

    expect($p1->slug)->toBe('common-slug')
        ->and($p2->slug)->toBe('common-slug');
});

test('organization can be accessed by uuid', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_user_id' => $user->id]);

    actingAs($user)
        ->getJson("/api/organizations/{$org->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $org->id);
});

test('organization can be accessed by slug', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create([
        'owner_user_id' => $user->id,
        'slug' => 'my-org',
    ]);

    actingAs($user)
        ->getJson('/api/organizations/my-org')
        ->assertOk()
        ->assertJsonPath('data.id', $org->id);
});

test('nested project can be accessed by slug', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create([
        'owner_user_id' => $user->id,
        'slug' => 'my-org',
    ]);
    $project = Project::factory()->create([
        'organization_id' => $org->id,
        'slug' => 'my-project',
        'name' => 'My Project',
    ]);

    actingAs($user)
        ->getJson('/api/organizations/my-org/projects/my-project')
        ->assertOk()
        ->assertJsonPath('data.id', $project->id);
});

test('nested project cannot be accessed via wrong organization', function () {
    $user = User::factory()->create();
    $org1 = Organization::factory()->create(['owner_user_id' => $user->id, 'slug' => 'org-1']);
    $org2 = Organization::factory()->create(['owner_user_id' => $user->id, 'slug' => 'org-2']);

    $project = Project::factory()->create([
        'organization_id' => $org1->id,
        'slug' => 'project-1',
        'name' => 'Project 1',
    ]);

    // Access project-1 via org-2
    actingAs($user)
        ->getJson('/api/organizations/org-2/projects/project-1')
        ->assertNotFound();
});
