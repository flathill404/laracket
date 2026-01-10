<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('requests a reset password link', function () {
    Notification::fake();
    $user = User::factory()->create();

    $response = postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertOk();
    Notification::assertSentTo(
        $user,
        ResetPassword::class
    );
});

it('resets password with valid token', function () {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertOk();
    assertGuest();

    $loginResponse = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'new-password',
    ]);

    $loginResponse->assertOk();
});

it('does not reset password with invalid token', function () {
    $user = User::factory()->create();

    $response = postJson('/api/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});
