<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('allows organization member to view the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('view', $team))->toBeTrue();
});

it('denies outsider to view the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();

    expect($user->can('view', $team))->toBeFalse();
});

it('allows organization owner to update the team', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $team = Team::factory()->for($organization)->create();

    expect($user->can('update', $team))->toBeTrue();
});

it('allows team leader to update the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    // Assuming team leader logic
    $team->users()->attach($user, ['role' => TeamRole::Leader]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('update', $team))->toBeTrue();
});

it('denies team member to update the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Member]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('update', $team))->toBeFalse();
});

it('allows organization owner to delete the team', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $team = Team::factory()->for($organization)->create();

    expect($user->can('delete', $team))->toBeTrue();
});

it('denies team leader to delete the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Leader]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('delete', $team))->toBeFalse();
});

it('allows team leader to add members', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Leader]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('add_member', $team))->toBeTrue();
});

it('denies team member to add members', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Member]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('add_member', $team))->toBeFalse();
});

it('allows team leader to remove members', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Leader]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('remove_member', $team))->toBeTrue();
});

it('allows team leader to update member roles', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->for($organization)->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['role' => TeamRole::Leader]);
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('update_member_role', $team))->toBeTrue();
});
