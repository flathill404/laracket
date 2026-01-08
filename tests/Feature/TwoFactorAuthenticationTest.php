<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication');

        $response->assertOk();

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_user_can_get_two_factor_qr_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication');

        $response = $this->getJson('/user/two-factor-qr-code');

        $response->assertOk();
        $this->assertStringContainsString('svg', $response->content());
    }

    public function test_user_can_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication');

        $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->postJson('/user/confirmed-two-factor-authentication', [
            'code' => '123456',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_user_can_authenticate_with_two_factor_code(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('dummy-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['dummy-recovery-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertJson(['two_factor' => true]);
        $this->assertGuest();

        $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->postJson('/two-factor-challenge', [
            'code' => '123456',
        ]);

        $response->assertNoContent();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_authenticate_with_recovery_code(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('dummy-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['valid-recovery-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->postJson('/two-factor-challenge', [
            'recovery_code' => 'valid-recovery-code',
        ]);

        $response->assertNoContent();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->deleteJson('/user/two-factor-authentication');

        $response->assertOk();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}
