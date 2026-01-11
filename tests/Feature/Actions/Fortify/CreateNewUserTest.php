<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('creates a new user', function () {
    $action = app(CreateNewUser::class);
    $input = [
        'name' => 'Power Chan',
        'display_name' => 'Power Chan',
        'email' => 'power@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $user = $action->create($input);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Power Chan');
    expect($user->email)->toBe('power@example.com');
    expect(Hash::check('password123', $user->password))->toBeTrue();
    assertDatabaseHas('users', [
        'email' => 'power@example.com',
    ]);
});

it('validates password confirmation', function () {
    $action = app(CreateNewUser::class);
    $input = [
        'name' => 'Denji',
        'display_name' => 'Denji',
        'email' => 'denji@example.com',
        'password' => 'password123',
        'password_confirmation' => 'wrong-password',
    ];

    expect(fn () => $action->create($input))
        ->toThrow(ValidationException::class);
});

it('validates password rules', function () {
    $action = app(CreateNewUser::class);
    $input = [
        'name' => 'Aki',
        'display_name' => 'Aki',
        'email' => 'aki@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ];

    expect(fn () => $action->create($input))
        ->toThrow(ValidationException::class);
});
