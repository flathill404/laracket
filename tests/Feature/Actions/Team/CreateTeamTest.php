<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\CreateTeam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_team(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $member = User::factory()->create();
        $action = new CreateTeam;

        $data = [
            'name' => 'Test Team',
            'display_name' => 'Test Team Display',
            'members' => [$member->id],
        ];

        $team = $action->create($user, $organization, $data);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Test Team',
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }
}
