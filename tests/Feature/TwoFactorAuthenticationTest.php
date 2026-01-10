<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('enables two factor authentication', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    $response->assertOk();

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_recovery_codes)->not->toBeNull();
});

it('gets two factor qr code', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    $response = getJson('/user/two-factor-qr-code');

    $response->assertOk();
    expect($response->content())->toContain('svg');
});

it('confirms two factor authentication', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    mock(TwoFactorAuthenticationProvider::class, function ($mock) {
        $mock->shouldReceive('verify')->andReturn(true);
    });

    $response = postJson('/user/confirmed-two-factor-authentication', [
        'code' => '123456',
    ]);

    $response->assertOk();

    $user->refresh();
    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

it('authenticates the user with two factor code', function () {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('dummy-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['dummy-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = postJson('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertJson(['two_factor' => true]);
    assertGuest();

    mock(TwoFactorAuthenticationProvider::class, function ($mock) {
        $mock->shouldReceive('verify')->andReturn(true);
    });

    $response = postJson('/two-factor-challenge', [
        'code' => '123456',
    ]);

    $response->assertNoContent();
    assertAuthenticatedAs($user);
});

it('authenticates the user with recovery code', function () {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('dummy-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['valid-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    postJson('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = postJson('/two-factor-challenge', [
        'recovery_code' => 'valid-recovery-code',
    ]);

    $response->assertNoContent();
    assertAuthenticatedAs($user);
});

it('disables two factor authentication', function () {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('secret'),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->deleteJson('/user/two-factor-authentication');

    $response->assertOk();

    $user->refresh();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});
