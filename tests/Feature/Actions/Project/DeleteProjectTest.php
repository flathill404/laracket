<?php

use App\Actions\Project\DeleteProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(LazilyRefreshDatabase::class);

it('deletes a project', function () {
    $project = Project::factory()->create();
    $user = User::factory()->create();
    $action = new DeleteProject;

    $action($user, $project);

    assertModelMissing($project);
});
