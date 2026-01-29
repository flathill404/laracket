<?php

declare(strict_types=1);

use App\Actions\Team\DeleteTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(LazilyRefreshDatabase::class);

describe('DeleteTeam', function () {
    it('deletes a team', function () {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $action = new DeleteTeam;

        $action($user, $team);

        assertModelMissing($team);
    });
});
