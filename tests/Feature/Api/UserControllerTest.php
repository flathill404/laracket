<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('show', function () {
    it('returns current user profile', function () {
        $user = User::factory()->create();
        actingAs($user);

        getJson('/api/user')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    });

    it('denies access if not authenticated', function () {
        getJson('/api/user')
            ->assertUnauthorized();
    });
});
