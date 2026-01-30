<?php

declare(strict_types=1);

use App\Actions\Team\CreateTeam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

describe('CreateTeam', function () {
    it('creates a team', function () {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $member = User::factory()->create();
        $action = new CreateTeam;

        $input = [
            'slug' => 'test-team',
            'name' => 'Test Team Display',
            'members' => [$member->id],
        ];

        $team = $action($user, $organization, $input);

        assertDatabaseHas('teams', [
            'id' => $team->id,
            'slug' => 'test-team',
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

        expect(fn () => $action($user, $organization, [
            'slug' => 'invalid-name!',
            'name' => 'Valid Display',
        ]))->toThrow(ValidationException::class);

        expect(fn () => $action($user, $organization, [
            'slug' => 'valid-name',
            'name' => str_repeat('a', 51),
        ]))->toThrow(ValidationException::class);
    });
});
