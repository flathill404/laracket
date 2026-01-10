<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('resets the user password', function () {
    $user = User::factory()->make([
        'password' => Hash::make('old-password'),
    ]);
    $action = new ResetUserPassword;
    $newPassword = 'New-Super-Power-Password-123';

    $action->reset($user, [
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $user->refresh();
    $this->assertTrue(
        Hash::check($newPassword, $user->password),
        'The user password was not reset correctly.'
    );
});

it('validates password rules', function () {
    $user = User::factory()->make();
    $action = new ResetUserPassword;

    $this->expectException(ValidationException::class);

    $action->reset($user, [
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);
});
