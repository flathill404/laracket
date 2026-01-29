<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

describe('UserAvatarUpload', function () {
    test('user can upload avatar', function () {
        Storage::fake();
        $user = User::factory()->create();

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==';

        $response = $this->actingAs($user)
            ->postJson('/api/user/avatar', [
                'avatar' => $base64Image,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'email', 'avatar_url']]);

        $user->refresh();
        expect($user->avatar_path)->not->toBeNull();
        Storage::assertExists($user->avatar_path);

        // Check if URL is correct in response
        $expectedUrl = Storage::url($user->avatar_path);
        expect($response->json('data.avatar_url'))->toBe($expectedUrl);
    });

    test('upload fails with invalid data', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/user/avatar', [
                'avatar' => 'not-a-base64-image',
            ]);

        $response->assertStatus(422);
    });

    test('upload fails with unsupported image type', function () {
        $user = User::factory()->create();

        // bmp is not in allow list
        $base64Image = 'data:image/bmp;base64,Qk02AAAAAAAAADYAAAAoAAAAAQAAAAEAAAABABgAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAA/wAA';

        $response = $this->actingAs($user)
            ->postJson('/api/user/avatar', [
                'avatar' => $base64Image,
            ]);

        $response->assertStatus(422);
    });

    test('old avatar is deleted when new one is uploaded', function () {
        Storage::fake();
        Queue::fake();

        $user = User::factory()->create();
        $oldPath = 'avatars/'.$user->id.'/old.png';
        Storage::put($oldPath, 'content');
        $user->update(['avatar_path' => $oldPath]);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==';

        $this->actingAs($user)
            ->postJson('/api/user/avatar', [
                'avatar' => $base64Image,
            ]);

        Storage::assertMissing($oldPath);
        $user->refresh();
        Storage::assertExists($user->avatar_path);
    });
});
