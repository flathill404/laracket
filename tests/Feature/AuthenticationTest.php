<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

it('retrieves sanctum csrf cookie', function () {
    $response = get('/sanctum/csrf-cookie');

    $response->assertNoContent();
});

it('authenticates the user', function () {
    $user = User::factory()->create();

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    assertAuthenticated();
});

it('does not authenticate with invalid password', function () {
    $user = User::factory()->create();

    postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    assertGuest();
});

it('logs out the user', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = postJson('/api/logout');

    $response->assertNoContent();
    assertGuest();
});
