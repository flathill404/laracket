<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns false for initial password confirmation status', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/user/confirmed-password-status');

    $response->assertOk()
        ->assertJson(['confirmed' => false]);
});

it('confirms the password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/user/confirm-password', [
            'password' => 'password',
        ]);

    $response->assertCreated();
    $this->getJson('/user/confirmed-password-status')
        ->assertJson(['confirmed' => true]);
});

it('fails password confirmation with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/user/confirm-password', [
            'password' => 'wrong-password',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
    $this->getJson('/user/confirmed-password-status')
        ->assertJson(['confirmed' => false]);
});
