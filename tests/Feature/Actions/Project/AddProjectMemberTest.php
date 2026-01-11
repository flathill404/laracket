<?php

use App\Actions\Project\AddProjectMember;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('adds a member to a project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);

    $action = new AddProjectMember;

    $action($project, $user);

    assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
});

it('validates user belongs to organization', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    // User is NOT added to organization

    $action = new AddProjectMember;

    expect(fn () => $action($project, $user))
        ->toThrow(ValidationException::class);
});

it('validates user is not already a member', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);
    $project->assignedUsers()->attach($user);

    $action = new AddProjectMember;

    expect(fn () => $action($project, $user))
        ->toThrow(ValidationException::class);
});
