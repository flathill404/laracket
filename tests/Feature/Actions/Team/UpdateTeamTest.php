<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\UpdateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_team(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $member = User::factory()->create();
        $action = new UpdateTeam;

        $data = [
            'name' => 'Updated Team Name',
            'members' => [$member->id],
        ];

        $action->update($user, $team, $data);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Team Name',
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }
}
