<?php

use App\Actions\Team\UpdateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates a team', function () {
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
});

it('validates team update', function () {
    $team = Team::factory()->create();
    $action = new UpdateTeam;

    expect(fn () => $action->update($team, [
        'name' => 'Invalid Name!',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action->update($team, [
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
