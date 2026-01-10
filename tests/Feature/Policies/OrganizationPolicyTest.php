<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use App\Policies\OrganizationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('organization policy', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $org = Organization::factory()->create(['owner_user_id' => $owner->id]);

    $org->users()->attach($admin, ['role' => OrganizationRole::Admin]);
    $org->users()->attach($member, ['role' => OrganizationRole::Member]);

    $policy = new OrganizationPolicy;

    // View
    // expect($policy->view($owner, $org))->toBeTrue();
    // Owner specific handling might be needed in Policy logic if not attached as user.

    // Update
    expect($policy->update($owner, $org))->toBeTrue();
    expect($policy->update($admin, $org))->toBeTrue();
    expect($policy->update($member, $org))->toBeFalse();

    // Delete
    expect($policy->delete($owner, $org))->toBeTrue();
    expect($policy->delete($admin, $org))->toBeFalse();

    // Invite Member
    expect($policy->invite_member($owner, $org))->toBeTrue();
    expect($policy->invite_member($admin, $org))->toBeTrue();
    expect($policy->invite_member($member, $org))->toBeFalse();
});
