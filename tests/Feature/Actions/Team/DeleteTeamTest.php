<?php

use App\Actions\Team\DeleteTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes a team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $action = new DeleteTeam;

    $action->delete($user, $team);

    $this->assertModelMissing($team);
});
