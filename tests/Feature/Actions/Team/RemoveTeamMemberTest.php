<?php

use App\Actions\Team\RemoveTeamMember;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

it('removes a member from a team', function () {
    $organization = Organization::factory()->create();
    $team = Team::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);
    $team->users()->attach($user, ['role' => TeamRole::Member]);

    $action = new RemoveTeamMember;

    $action($team, $user);

    assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);
});
