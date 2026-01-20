<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_path ? Storage::url($this->avatar_path) : null,
            'created_at' => $this->created_at,
            'role' => $this->whenPivotLoaded('organization_user', function () {
                return $this->pivot->role; // @phpstan-ignore-line
            }),
            'team_role' => $this->whenPivotLoaded('team_user', function () {
                return $this->pivot->role; // @phpstan-ignore-line
            }),

            $this->mergeWhen(
                $isOwner,
                [
                    'email_verified_at' => $this->email_verified_at,
                    'two_factor_status' => $this->twoFactorStatus(),
                ],
            ),
        ];
    }
}
