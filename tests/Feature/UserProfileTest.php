<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_their_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_user_can_update_profile_information(): void
    {
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
    }

    public function test_user_can_update_password(): void
    {
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
    }

    public function test_password_update_fails_if_current_password_is_incorrect(): void
    {
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
    }
}
