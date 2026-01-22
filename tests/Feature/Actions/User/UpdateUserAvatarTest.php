<?php

declare(strict_types=1);

use App\Actions\User\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(LazilyRefreshDatabase::class);

describe('UpdateUserAvatar', function () {
    it('can update user avatar', function () {
        Storage::fake();

        $user = User::factory()->create();
        $action = new UpdateUserAvatar;

        $input = [
            'avatar' => 'data:image/png;base64,'.base64_encode('fake-image-content'),
        ];

        $updatedUser = $action($user, $input);

        expect($updatedUser->avatar_path)->not->toBeNull();
        Storage::assertExists($updatedUser->avatar_path);
    });

    it('throws validation exception for invalid data uri', function () {
        $user = User::factory()->create();
        $action = new UpdateUserAvatar;

        $input = [
            'avatar' => 'invalid-data-uri',
        ];

        $action($user, $input);
    })->throws(ValidationException::class, 'Invalid Image Data URI');

    it('throws validation exception for unsupported image type', function () {
        $user = User::factory()->create();
        $action = new UpdateUserAvatar;

        $input = [
            'avatar' => 'data:image/bmp;base64,'.base64_encode('fake-image-content'),
        ];

        $action($user, $input);
    })->throws(ValidationException::class, 'Unsupported image type');

    it('deletes old avatar when updating', function () {
        Storage::fake();

        $user = User::factory()->create([
            'avatar_path' => 'avatars/old-avatar.png',
        ]);
        Storage::put('avatars/old-avatar.png', 'old-content');

        $action = new UpdateUserAvatar;
        $input = [
            'avatar' => 'data:image/png;base64,'.base64_encode('new-image-content'),
        ];

        $action($user, $input);

        Storage::assertMissing('avatars/old-avatar.png');
        Storage::assertExists($user->refresh()->avatar_path);
    });
});
