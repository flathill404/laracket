<?php

use App\Actions\Team\UpdateTeamMemberRole;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('updates a team member role', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);
    $team->users()->attach($user, ['role' => TeamRole::Member]);

    $action = new UpdateTeamMemberRole;

    $action($team, $user, TeamRole::Leader);

    assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => TeamRole::Leader->value,
    ]);
});

it('validates user is a member of the team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);
    // User is NOT added to team

    $action = new UpdateTeamMemberRole;

    expect(fn () => $action($team, $user, TeamRole::Leader))
        ->toThrow(ValidationException::class);
});
