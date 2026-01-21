<?php

use App\Actions\User\DeleteUserAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

describe('DeleteUserAvatar', function () {
    it('can delete user avatar', function () {
        Storage::fake();

        $user = User::factory()->create([
            'avatar_path' => 'avatars/test-avatar.png',
        ]);
        Storage::put('avatars/test-avatar.png', 'content');

        $action = new DeleteUserAvatar;

        $action($user);

        expect($user->refresh()->avatar_path)->toBeNull();
        Storage::assertMissing('avatars/test-avatar.png');
    });

    it('handles user without avatar', function () {
        Storage::fake();

        $user = User::factory()->create([
            'avatar_path' => null,
        ]);

        $action = new DeleteUserAvatar;

        $action($user);

        expect($user->refresh()->avatar_path)->toBeNull();
    });
});
