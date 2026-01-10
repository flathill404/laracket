<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user', function () {
    $response = $this->postJson('/register', [
        'name' => 'Power Chan',
        'display_name' => 'Power Chan',
        'email' => 'power@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated();
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'power@example.com',
    ]);
});

it('fails registration if passwords do not match', function () {
    $response = $this->postJson('/register', [
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

    $response = $this->postJson('/register', [
        'name' => 'Another Power',
        'display_name' => 'Another Power',
        'email' => 'power@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});
