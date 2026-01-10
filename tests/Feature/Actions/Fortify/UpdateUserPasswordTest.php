<?php

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates the user password', function () {
    $user = User::factory()->make([
        'password' => Hash::make('old-password'),
    ]);
    $this->actingAs($user);
    $action = new UpdateUserPassword;

    $newPassword = 'New-Super-Strong-Password-999';
    $action->update($user, [
        'current_password' => 'old-password',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $user->refresh();
    $this->assertTrue(
        Hash::check($newPassword, $user->password),
        'The user password was not updated correctly.'
    );
});

it('validates the current password', function () {
    $user = User::factory()->make([
        'password' => Hash::make('old-password'),
    ]);
    $this->actingAs($user);
    $action = new UpdateUserPassword;

    $this->expectException(ValidationException::class);

    $action->update($user, [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);
});

it('validates new password rules', function () {
    $user = User::factory()->make();
    $this->actingAs($user);
    $action = new UpdateUserPassword;

    $this->expectException(ValidationException::class);

    $action->update($user, [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);
});
