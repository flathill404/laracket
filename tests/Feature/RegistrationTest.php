<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
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
    }

    public function test_registration_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Power Chan',
            'display_name' => 'Power Chan',
            'email' => 'power@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
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
    }
}
