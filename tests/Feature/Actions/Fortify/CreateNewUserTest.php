<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates a new user', function () {
    $action = $this->app->make(CreateNewUser::class);
    $input = [
        'name' => 'Power Chan',
        'display_name' => 'Power Chan',
        'email' => 'power@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $user = $action->create($input);

    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals('Power Chan', $user->name);
    $this->assertEquals('power@example.com', $user->email);
    $this->assertTrue(Hash::check('password123', $user->password));
    $this->assertDatabaseHas('users', [
        'email' => 'power@example.com',
    ]);
});

it('validates password confirmation', function () {
    $action = $this->app->make(CreateNewUser::class);
    $input = [
        'name' => 'Denji',
        'display_name' => 'Denji',
        'email' => 'denji@example.com',
        'password' => 'password123',
        'password_confirmation' => 'wrong-password',
    ];

    $this->expectException(ValidationException::class);

    $action->create($input);
});

it('validates password rules', function () {
    $action = $this->app->make(CreateNewUser::class);
    $input = [
        'name' => 'Aki',
        'display_name' => 'Aki',
        'email' => 'aki@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ];

    $this->expectException(ValidationException::class);

    $action->create($input);
});
