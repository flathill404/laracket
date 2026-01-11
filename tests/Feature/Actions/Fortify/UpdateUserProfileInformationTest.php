<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(LazilyRefreshDatabase::class);

it('updates profile information', function () {
    $user = User::factory()->make([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $action = new UpdateUserProfileInformation;

    $action->update($user, [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

it('validates email uniqueness', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->make(['email' => 'original@example.com']);
    $action = new UpdateUserProfileInformation;

    expect(fn () => $action->update($user, [
        'name' => 'New Name',
        'email' => 'taken@example.com',
    ]))->toThrow(ValidationException::class);
});

it('updates name with the same email', function () {
    $user = User::factory()->make(['email' => 'me@example.com']);
    $action = new UpdateUserProfileInformation;

    $action->update($user, [
        'name' => 'New Name',
        'email' => 'me@example.com',
    ]);

    $user->refresh();
    expect($user->name)->toBe('New Name');
});
