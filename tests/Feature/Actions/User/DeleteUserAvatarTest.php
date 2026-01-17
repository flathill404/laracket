<?php

namespace Tests\Feature\Actions\User;

use App\Actions\User\DeleteUserAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteUserAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_user_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_path' => 'avatars/test-avatar.png',
        ]);
        Storage::disk('public')->put('avatars/test-avatar.png', 'content');

        $action = new DeleteUserAvatar;

        $action($user);

        $this->assertNull($user->refresh()->avatar_path);
        Storage::disk('public')->assertMissing('avatars/test-avatar.png');
    }

    public function test_handles_user_without_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_path' => null,
        ]);

        $action = new DeleteUserAvatar;

        $action($user);

        $this->assertNull($user->refresh()->avatar_path);
    }
}
