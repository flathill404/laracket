<?php

use App\Actions\Team\CreateTeam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

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
});

it('validates team creation', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $action = new CreateTeam;

    expect(fn () => $action->create($user, $organization, [
        'name' => 'Invalid Name!',
        'display_name' => 'Valid Display',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action->create($user, $organization, [
        'name' => 'valid-name',
        'display_name' => str_repeat('a', 51),
    ]))->toThrow(ValidationException::class);
});
