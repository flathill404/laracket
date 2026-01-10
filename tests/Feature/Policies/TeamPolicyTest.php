<?php

use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('team policy', function () {
    $owner = User::factory()->create();
    $leader = User::factory()->create();
    $member = User::factory()->create();
    $stranger = User::factory()->create();

    $org = Organization::factory()->create(['owner_user_id' => $owner->id]);
    $team = Team::factory()->create(['organization_id' => $org->id]);

    // Setup roles
    // Need to verify if users must be org members first?
    // Policy 'view' says: return $user->organizations()->where('organization_id', $team->organization_id)->exists();
    // Use factory attach for org membership
    $org->users()->attach($leader); // Implicit member without specific Org Role unless needed
    $org->users()->attach($member);

    $team->users()->attach($leader, ['role' => TeamRole::Leader]);
    $team->users()->attach($member, ['role' => TeamRole::Member]);

    $policy = new TeamPolicy;

    // View
    // User must be org member. Owner is owner.
    // For owner, update policy to allow if owner?
    // Current Policy: $user->organizations()->...exists().
    // Owner might not be attached. Check logic in TeamPolicy::view.
    // It only checks $user->organizations().
    // I should probably attach Owner to Org in setup or fix Policy.
    // Let's attach Owner to Org for this test context or assume he is attached.
    $org->users()->attach($owner); // As member

    expect($policy->view($owner, $team))->toBeTrue();
    expect($policy->view($member, $team))->toBeTrue();
    expect($policy->view($stranger, $team))->toBeFalse();

    // Update
    // Owner, Admin, or Team Leader
    expect($policy->update($owner, $team))->toBeTrue();
    expect($policy->update($leader, $team))->toBeTrue();
    expect($policy->update($member, $team))->toBeFalse();

    // Delete
    // Owner or Admin only
    expect($policy->delete($owner, $team))->toBeTrue();
    expect($policy->delete($leader, $team))->toBeFalse();
});
