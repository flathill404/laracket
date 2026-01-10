<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

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

    $this->assertEquals('New Name', $user->name);
    $this->assertEquals('new@example.com', $user->email);
});

it('validates email uniqueness', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->make(['email' => 'original@example.com']);
    $action = new UpdateUserProfileInformation;

    $this->expectException(ValidationException::class);

    $action->update($user, [
        'name' => 'New Name',
        'email' => 'taken@example.com',
    ]);
});

it('updates name with the same email', function () {
    $user = User::factory()->make(['email' => 'me@example.com']);
    $action = new UpdateUserProfileInformation;

    $action->update($user, [
        'name' => 'New Name',
        'email' => 'me@example.com',
    ]);

    $user->refresh();
    $this->assertEquals('New Name', $user->name);
});
