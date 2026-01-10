<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('returns false for initial password confirmation status', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->getJson('/user/confirmed-password-status');

    $response->assertOk()
        ->assertJson(['confirmed' => false]);
});

it('confirms the password', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->postJson('/user/confirm-password', [
            'password' => 'password',
        ]);

    $response->assertCreated();
    getJson('/user/confirmed-password-status')
        ->assertJson(['confirmed' => true]);
});

it('fails password confirmation with invalid password', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->postJson('/user/confirm-password', [
            'password' => 'wrong-password',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
    getJson('/user/confirmed-password-status')
        ->assertJson(['confirmed' => false]);
});
