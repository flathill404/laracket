<?php

namespace Tests\Feature\Actions\User;

use App\Actions\User\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateUserAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_user_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $action = new UpdateUserAvatar();

        $input = [
            'avatar' => 'data:image/png;base64,'.base64_encode('fake-image-content'),
        ];

        $updatedUser = $action($user, $input);

        $this->assertNotNull($updatedUser->avatar_path);
        Storage::disk('public')->assertExists($updatedUser->avatar_path);
    }

    public function test_throws_validation_exception_for_invalid_data_uri(): void
    {
        $user = User::factory()->create();
        $action = new UpdateUserAvatar();

        $input = [
            'avatar' => 'invalid-data-uri',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Image Data URI');

        $action($user, $input);
    }

    public function test_throws_validation_exception_for_unsupported_image_type(): void
    {
        $user = User::factory()->create();
        $action = new UpdateUserAvatar();

        $input = [
            'avatar' => 'data:image/bmp;base64,'.base64_encode('fake-image-content'),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported image type');

        $action($user, $input);
    }

    public function test_deletes_old_avatar_when_updating(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_path' => 'avatars/old-avatar.png',
        ]);
        Storage::disk('public')->put('avatars/old-avatar.png', 'old-content');

        $action = new UpdateUserAvatar();
        $input = [
            'avatar' => 'data:image/png;base64,'.base64_encode('new-image-content'),
        ];

        $action($user, $input);

        Storage::disk('public')->assertMissing('avatars/old-avatar.png');
        Storage::disk('public')->assertExists($user->refresh()->avatar_path);
    }
}
