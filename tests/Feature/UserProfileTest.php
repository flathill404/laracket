<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

describe('UserProfile', function () {
    it('retrieves user profile', function () {
        $user = User::factory()->create();

        $response = actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    });

    it('updates profile information', function () {
        $user = User::factory()->create();

        $response = actingAs($user)
            ->putJson('/api/user/profile-information', [
                'slug' => 'super-power',
                'email' => 'super_power@example.com',
            ]);

        $response->assertOk();
        assertDatabaseHas('users', [
            'id' => $user->id,
            'slug' => 'super-power',
            'email' => 'super_power@example.com',
        ]);
    });

    it('updates password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'old-password',
                'password' => 'new-strong-password',
                'password_confirmation' => 'new-strong-password',
            ]);

        $response->assertOk();

        $user->refresh();
        expect(Hash::check('new-strong-password', $user->password))->toBeTrue();
    });

    it('fails password update if current password is incorrect', function () {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['current_password']);
    });
});
