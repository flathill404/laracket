<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('allows organization owner to view the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('view', $project))->toBeTrue();
});

it('allows organization admin to view the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('view', $project))->toBeTrue();
});

it('allows project member to view the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    // Assuming project member logic, usually implies org membership too for this context,
    // but specific logic for hasProjectAccess should be covered.
    // Assuming simple project membership here:
    $project->assignedUsers()->attach($user);

    expect($user->can('view', $project))->toBeTrue();
});

it('allows organization owner to update the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('update', $project))->toBeTrue();
});

it('allows organization admin to update the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('update', $project))->toBeTrue();
});

it('denies project member to update the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('update', $project))->toBeFalse();
});

it('allows organization owner to delete the project', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('delete', $project))->toBeTrue();
});

it('allows organization admin to delete the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('delete', $project))->toBeTrue();
});

it('denies project member to delete the project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('delete', $project))->toBeFalse();
});

it('allows project member to create tickets', function () {
    // create_ticket delegates to view
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('create_ticket', $project))->toBeTrue();
});

it('allows organization owner to add members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('add_member', $project))->toBeTrue();
});

it('denies project member to add members', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('add_member', $project))->toBeFalse();
});

it('allows organization owner to remove members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('remove_member', $project))->toBeTrue();
});

it('denies project member to remove members', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('remove_member', $project))->toBeFalse();
});

it('allows organization owner to attach team', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('attach_team', $project))->toBeTrue();
});

it('denies project member to attach team', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('attach_team', $project))->toBeFalse();
});

it('allows organization owner to detach team', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();

    expect($user->can('detach_team', $project))->toBeTrue();
});

it('denies project member to detach team', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    expect($user->can('detach_team', $project))->toBeFalse();
});
