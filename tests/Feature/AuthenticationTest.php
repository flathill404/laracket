<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
  use RefreshDatabase;

  public function test_sanctum_csrf_cookie_can_be_retrieved(): void
  {
    $response = $this->get('/sanctum/csrf-cookie');

    $response->assertNoContent();
  }

  public function test_users_can_authenticate_using_the_login_screen(): void
  {
    $user = User::factory()->create();

    $response = $this->postJson('/login', [
      'email' => $user->email,
      'password' => 'password'
    ]);

    $response->assertOk();
    $this->assertAuthenticated();
  }

  public function test_users_can_not_authenticate_with_invalid_password(): void
  {
    $user = User::factory()->create();

    $this->postJson('/login', [
      'email' => $user->email,
      'password' => 'wrong-password',
    ]);

    $this->assertGuest();
  }

  public function test_users_can_logout(): void
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson('/logout');

    $response->assertNoContent();
    $this->assertGuest();
  }
}
