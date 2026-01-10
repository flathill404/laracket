<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('retrieves user profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/user');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

it('updates profile information', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->putJson('/user/profile-information', [
            'name' => 'Super Power',
            'email' => 'super_power@example.com',
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Super Power',
        'email' => 'super_power@example.com',
    ]);
});

it('updates password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)
        ->putJson('/user/password', [
            'current_password' => 'old-password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ]);

    $response->assertOk();

    $user->refresh();
    $this->assertTrue(
        Hash::check('new-strong-password', $user->password),
        'The password was not updated correctly.'
    );
});

it('fails password update if current password is incorrect', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)
        ->putJson('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['current_password']);
});
