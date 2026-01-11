<?php

use App\Actions\Team\CreateTeam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('creates a team', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $member = User::factory()->create();
    $action = new CreateTeam;

    $input = [
        'name' => 'TestTeam',
        'display_name' => 'Test Team Display',
        'members' => [$member->id],
    ];

    $team = $action($user, $organization, $input);

    assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'TestTeam',
        'organization_id' => $organization->id,
    ]);

    assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);
});

it('validates team creation', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $action = new CreateTeam;

    expect(fn() => $action($user, $organization, [
        'name' => 'Invalid Name!',
        'display_name' => 'Valid Display',
    ]))->toThrow(ValidationException::class);

    expect(fn() => $action($user, $organization, [
        'name' => 'valid-name',
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
