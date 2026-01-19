<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateUserAvatar
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(User $user, array $input): User
    {
        Validator::make($input, $this->rules())->validate();

        /** @var string $data */
        $data = $input['avatar'];

        [$decodedData, $extension] = $this->validateAndParseAvatar($data);

        // Generate filename
        $filename = Str::uuid().'.'.$extension;
        $path = "avatars/{$user->id}/{$filename}";

        // Delete old avatar if exists
        if ($user->avatar_path && Storage::exists($user->avatar_path)) {
            Storage::delete($user->avatar_path);
        }

        // Store new avatar
        Storage::put($path, $decodedData);

        // Update user
        $user->forceFill([
            'avatar_path' => $path,
        ])->save();

        return $user;
    }

    /**
     * @return array<string, array<string>>
     */
    protected function rules(): array
    {
        return [
            'avatar' => ['required', 'string'],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     *
     * @throws ValidationException
     */
    protected function validateAndParseAvatar(string $data): array
    {
        // Parse Data URI
        if (! preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            throw ValidationException::withMessages([
                'avatar' => ['Invalid Image Data URI'],
            ]);
        }

        $dataContent = substr($data, strpos($data, ',') + 1);
        $extension = strtolower($type[1]); // jpg, png, gif

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw ValidationException::withMessages([
                'avatar' => ['Unsupported image type'],
            ]);
        }

        $decodedData = base64_decode($dataContent, true);

        if ($decodedData === false) {
            throw ValidationException::withMessages([
                'avatar' => ['Base64 decode failed'],
            ]);
        }

        return [$decodedData, $extension];
    }
}
