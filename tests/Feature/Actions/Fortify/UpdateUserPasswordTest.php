<?php

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;

uses(LazilyRefreshDatabase::class);

it('updates the user password', function () {
    $user = User::factory()->make([
        'password' => Hash::make('old-password'),
    ]);
    actingAs($user);
    $action = new UpdateUserPassword;

    $newPassword = 'New-Super-Strong-Password-999';
    $action->update($user, [
        'current_password' => 'old-password',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $user->refresh();
    expect(Hash::check($newPassword, $user->password))->toBeTrue(
        'The user password was not updated correctly.'
    );
});

it('validates the current password', function () {
    $user = User::factory()->make([
        'password' => Hash::make('old-password'),
    ]);
    actingAs($user);
    $action = new UpdateUserPassword;

    expect(fn() => $action->update($user, [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]))->toThrow(ValidationException::class);
});

it('validates new password rules', function () {
    $user = User::factory()->make();
    actingAs($user);
    $action = new UpdateUserPassword;

    expect(fn() => $action->update($user, [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]))->toThrow(ValidationException::class);
});
