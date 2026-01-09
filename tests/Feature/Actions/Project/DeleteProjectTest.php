<?php

namespace Tests\Feature\Actions\Project;

use App\Actions\Project\DeleteProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_a_project(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();
        $action = new DeleteProject;

        $action->execute($user, $project);

        $this->assertModelMissing($project);
    }
}
