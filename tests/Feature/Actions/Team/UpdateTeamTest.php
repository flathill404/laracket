<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\UpdateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

        $input = [
            'name' => 'UpdatedTeamName',
            'members' => [$member->id],
        ];

        $action->update($team, $input);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'UpdatedTeamName',
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }

    public function test_it_validates_team_update(): void
    {
        $team = Team::factory()->create();
        $action = new UpdateTeam;

        $this->assertThrows(function () use ($action, $team) {
            $action->update($team, [
                'name' => 'Invalid Name!',
            ]);
        }, ValidationException::class);

        $this->assertThrows(function () use ($action, $team) {
            $action->update($team, [
                'display_name' => str_repeat('a', 51),
            ]);
        }, ValidationException::class);
    }
}
