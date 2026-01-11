<?php

use App\Actions\Team\UpdateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('updates a team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $member = User::factory()->create();
    $action = new UpdateTeam;

    $input = [
        'name' => 'UpdatedTeamName',
        'members' => [$member->id],
    ];

    $action($team, $input);

    assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'UpdatedTeamName',
    ]);

    assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);
});

it('validates team update', function () {
    $team = Team::factory()->create();
    $action = new UpdateTeam;

    expect(fn() => $action($team, [
        'name' => 'Invalid Name!',
    ]))->toThrow(ValidationException::class);

    expect(fn() => $action($team, [
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
