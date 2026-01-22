<?php

declare(strict_types=1);

use App\Actions\Project\RemoveProjectMember;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

it('removes a member from a project', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $project->assignedUsers()->attach($user);

    $action = new RemoveProjectMember;

    $action($project, $user);

    assertDatabaseMissing('project_user', [
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
});
