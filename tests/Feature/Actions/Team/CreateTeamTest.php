<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\CreateTeam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

        $input = [
            'name' => 'TestTeam',
            'display_name' => 'Test Team Display',
            'members' => [$member->id],
        ];

        $team = $action->create($user, $organization, $input);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'TestTeam',
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }

    public function test_it_validates_team_creation(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $action = new CreateTeam;

        $this->assertThrows(function () use ($action, $user, $organization) {
            $action->create($user, $organization, [
                'name' => 'Invalid Name!',
                'display_name' => 'Valid Display',
            ]);
        }, ValidationException::class);

        $this->assertThrows(function () use ($action, $user, $organization) {
            $action->create($user, $organization, [
                'name' => 'valid-name',
                'display_name' => str_repeat('a', 51),
            ]);
        }, ValidationException::class);
    }
}
