<?php

use App\Actions\Team\DeleteTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(RefreshDatabase::class);

it('deletes a team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $action = new DeleteTeam;

    $action->delete($user, $team);

    assertModelMissing($team);
});
