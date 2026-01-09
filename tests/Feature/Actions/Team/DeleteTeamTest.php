<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\DeleteTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_a_team(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $action = new DeleteTeam;

        $action->execute($user, $team);

        $this->assertModelMissing($team);
    }
}
