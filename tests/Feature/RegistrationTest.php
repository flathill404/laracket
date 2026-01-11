<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

it('registers a new user', function () {
    $response = postJson('/api/register', [
        'name' => 'Power Chan',
        'display_name' => 'Power Chan',
        'email' => 'power@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated();
    assertAuthenticated();
    assertDatabaseHas('users', [
        'email' => 'power@example.com',
    ]);
});

it('fails registration if passwords do not match', function () {
    $response = postJson('/api/register', [
        'name' => 'Power Chan',
        'display_name' => 'Power Chan',
        'email' => 'power@example.com',
        'password' => 'password',
        'password_confirmation' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('fails registration with duplicate email', function () {
    User::factory()->create([
        'email' => 'power@example.com',
    ]);

    $response = postJson('/api/register', [
        'name' => 'Another Power',
        'display_name' => 'Another Power',
        'email' => 'power@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});
