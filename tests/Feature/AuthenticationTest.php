<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('retrieves sanctum csrf cookie', function () {
    $response = $this->get('/sanctum/csrf-cookie');

    $response->assertNoContent();
});

it('authenticates the user', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    $this->assertAuthenticated();
});

it('does not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->postJson('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

it('logs out the user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson('/logout');

    $response->assertNoContent();
    $this->assertGuest();
});
