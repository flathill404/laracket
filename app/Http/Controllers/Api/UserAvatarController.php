<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Jobs\OptimizeUserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAvatarController
{
    public function update(Request $request): UserResource
    {
        $request->validate([
            'avatar' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $data = $request->input('avatar');

        // Parse Data URI
        if (! preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            abort(422, 'Invalid Image Data URI');
        }

        $data = substr($data, strpos($data, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (! in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            abort(422, 'Unsupported image type');
        }

        $data = base64_decode($data);

        if ($data === false) {
            abort(422, 'Base64 decode failed');
        }

        // Generate filename
        $filename = Str::uuid().'.'.$type;
        $path = "avatars/{$user->id}/{$filename}";

        // Delete old avatar if exists
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store new avatar
        Storage::disk('public')->put($path, $data);

        // Update user
        $user->forceFill([
            'avatar_path' => $path,
        ])->save();
        // Dispatch job
        OptimizeUserAvatar::dispatch($user);

        return new UserResource($user);
    }
}
