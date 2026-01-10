<?php

use App\Actions\Project\DeleteProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(RefreshDatabase::class);

it('deletes a project', function () {
    $project = Project::factory()->create();
    $user = User::factory()->create();
    $action = new DeleteProject;

    $action($user, $project);

    assertModelMissing($project);
});
