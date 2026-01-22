<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('allows user to view any organization', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', Organization::class))->toBeTrue();
});

it('allows organization member to view the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('view', $organization))->toBeTrue();
});

it('denies non-member to view the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    expect($user->can('view', $organization))->toBeFalse();
});

it('allows organization owner to update the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('update', $organization))->toBeTrue();
});

it('allows organization admin to update the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('update', $organization))->toBeTrue();
});

it('denies organization member to update the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('update', $organization))->toBeFalse();
});

it('allows organization owner to delete the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('delete', $organization))->toBeTrue();
});

it('denies organization admin to delete the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('delete', $organization))->toBeFalse();
});

it('denies organization member to delete the organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('delete', $organization))->toBeFalse();
});

it('allows organization owner to invite members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('invite_member', $organization))->toBeTrue();
});

it('allows organization admin to invite members', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('invite_member', $organization))->toBeTrue();
});

it('denies organization member to invite members', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('invite_member', $organization))->toBeFalse();
});

it('allows organization owner to create projects', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('create_project', $organization))->toBeTrue();
});

it('allows organization admin to create projects', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('create_project', $organization))->toBeTrue();
});

it('denies organization member to create projects', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('create_project', $organization))->toBeFalse();
});

it('allows organization owner to create teams', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('create_team', $organization))->toBeTrue();
});

it('allows organization admin to create teams', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('create_team', $organization))->toBeTrue();
});

it('denies organization member to create teams', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('create_team', $organization))->toBeFalse();
});

it('allows organization owner to remove members', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('remove_member', $organization))->toBeTrue();
});

it('allows organization admin to remove members', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('remove_member', $organization))->toBeTrue();
});

it('denies organization member to remove members', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('remove_member', $organization))->toBeFalse();
});

it('allows organization owner to update member roles', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);

    expect($user->can('update_member_role', $organization))->toBeTrue();
});

it('allows organization admin to update member roles', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

    expect($user->can('update_member_role', $organization))->toBeTrue();
});

it('denies organization member to update member roles', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

    expect($user->can('update_member_role', $organization))->toBeFalse();
});
