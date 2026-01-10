<?php

use App\Actions\Team\AddTeamMember;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('adds a member to a team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);

    $action = new AddTeamMember;

    $action($team, $user);

    assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => TeamRole::Member->value,
    ]);
});

it('validates user belongs to organization', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    // User is NOT added to organization

    $action = new AddTeamMember;

    expect(fn () => $action($team, $user))
        ->toThrow(ValidationException::class);
});

it('validates user is not already a member', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);
    $team->users()->attach($user, ['role' => TeamRole::Member]);

    $action = new AddTeamMember;

    expect(fn () => $action($team, $user))
        ->toThrow(ValidationException::class);
});
