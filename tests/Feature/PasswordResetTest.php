<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
  use RefreshDatabase;

  public function test_reset_password_link_can_be_requested(): void
  {
    Notification::fake();
    $user = User::factory()->create();

    $response = $this->postJson('/forgot-password', [
      'email' => $user->email,
    ]);

    $response->assertOk();
    Notification::assertSentTo(
      $user,
      ResetPassword::class
    );
  }

  public function test_password_can_be_reset_with_valid_token(): void
  {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = $this->postJson('/reset-password', [
      'token' => $token,
      'email' => $user->email,
      'password' => 'new-password',
      'password_confirmation' => 'new-password',
    ]);

    $response->assertOk();
    $this->assertGuest();

    $loginResponse = $this->postJson('/login', [
      'email' => $user->email,
      'password' => 'new-password',
    ]);

    $loginResponse->assertOk();
  }

  public function test_password_can_not_be_reset_with_invalid_token(): void
  {
    $user = User::factory()->create();

    $response = $this->postJson('/reset-password', [
      'token' => 'invalid-token',
      'email' => $user->email,
      'password' => 'new-password',
      'password_confirmation' => 'new-password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
  }
}
