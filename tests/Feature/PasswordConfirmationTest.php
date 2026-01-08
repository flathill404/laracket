<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_confirmation_status_is_false_initially(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/user/confirmed-password-status');

        $response->assertOk()
            ->assertJson(['confirmed' => false]);
    }

    public function test_user_can_confirm_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/user/confirm-password', [
                'password' => 'password',
            ]);

        $response->assertCreated();
        $this->getJson('/user/confirmed-password-status')
            ->assertJson(['confirmed' => true]);
    }

    public function test_password_confirmation_fails_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/user/confirm-password', [
                'password' => 'wrong-password',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
        $this->getJson('/user/confirmed-password-status')
            ->assertJson(['confirmed' => false]);
    }
}
