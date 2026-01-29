<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

describe('UserAvatarController', function () {
    describe('store', function () {
        it('can update avatar via API', function () {
            Storage::fake();
            $user = User::factory()->create();

            actingAs($user);

            $response = postJson('/api/user/avatar', [
                'avatar' => 'data:image/png;base64,'.base64_encode('fake-image-content'),
            ]);

            $response->assertOk();
            $response->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'avatar_url',
                ],
            ]);

            expect($user->refresh()->avatar_path)->not->toBeNull();
        });

        it('returns validation error for invalid data', function () {
            $user = User::factory()->create();

            actingAs($user);

            $response = postJson('/api/user/avatar', [
                'avatar' => 'invalid-data',
            ]);

            $response->assertStatus(422);
        });
    });

    describe('destroy', function () {
        it('can delete avatar via API', function () {
            Storage::fake();
            $user = User::factory()->create(['avatar_path' => 'avatars/delete-me.png']);
            Storage::put('avatars/delete-me.png', 'content');

            actingAs($user);

            $response = deleteJson('/api/user/avatar');

            $response->assertNoContent();
            expect($user->refresh()->avatar_path)->toBeNull();
            Storage::assertMissing('avatars/delete-me.png');
        });
    });
});
