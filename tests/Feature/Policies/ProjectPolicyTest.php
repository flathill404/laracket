<?php

use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project policy', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $teamMember = User::factory()->create();
    $stranger = User::factory()->create();

    $org = Organization::factory()->create(['owner_user_id' => $owner->id]);
    $project = Project::factory()->create(['organization_id' => $org->id]);

    // Direct assignment
    $project->assignedUsers()->attach($member);

    // Team assignment
    $team = Team::factory()->create(['organization_id' => $org->id]);
    $team->users()->attach($teamMember, ['role' => TeamRole::Member]);
    $project->assignedTeams()->attach($team);

    $policy = new ProjectPolicy;

    // View
    expect($policy->view($owner, $project))->toBeTrue();
    expect($policy->view($member, $project))->toBeTrue();
    expect($policy->view($teamMember, $project))->toBeTrue();
    expect($policy->view($stranger, $project))->toBeFalse();

    // Update / Delete
    expect($policy->update($owner, $project))->toBeTrue();
    expect($policy->update($member, $project))->toBeFalse();
});
