<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Queries\GetUserProjects;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GetUserProjects', function () {
    test('organization owner can see all projects', function () {
        $owner = User::factory()->create();
        $org = Organization::factory()->create(['owner_user_id' => $owner->id]);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $query = new GetUserProjects;
        $projects = $query($owner);

        expect($projects)->toHaveCount(1)
            ->and($projects->first()->id)->toBe($project->id);
    });

    test('organization admin can see all projects', function () {
        $admin = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($admin, ['role' => OrganizationRole::Admin]);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $query = new GetUserProjects;
        $projects = $query($admin);

        expect($projects)->toHaveCount(1)
            ->and($projects->first()->id)->toBe($project->id);
    });

    test('organization member cannot see projects not assigned', function () {
        $member = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($member, ['role' => OrganizationRole::Member]);
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $query = new GetUserProjects;
        $projects = $query($member);

        expect($projects)->toHaveCount(0);
    });

    test('assigned user can see project', function () {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        // User is part of org or not? logic doesn't strictly require it but usually they are.
        // Let's assume they are just a member or unrelated
        $project = Project::factory()->create(['organization_id' => $org->id]);
        $project->assignedUsers()->attach($user);

        $query = new GetUserProjects;
        $projects = $query($user);

        expect($projects)->toHaveCount(1)
            ->and($projects->first()->id)->toBe($project->id);
    });

    test('assigned team member can see project', function () {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $team->users()->attach($user, ['role' => OrganizationRole::Member]); // Role in team doesn't matter for visibility usually

        $project = Project::factory()->create(['organization_id' => $org->id]);
        $project->assignedTeams()->attach($team);

        $query = new GetUserProjects;
        $projects = $query($user);

        expect($projects)->toHaveCount(1)
            ->and($projects->first()->id)->toBe($project->id);
    });

    test('unrelated user cannot see project', function () {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $org->id]);

        $query = new GetUserProjects;
        $projects = $query($user);

        expect($projects)->toHaveCount(0);
    });
});
