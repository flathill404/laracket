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
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
            'avatar_url' => $this->avatar_path ? Storage::url($this->avatar_path) : null,
            'created_at' => $this->created_at,
            'role' => $this->whenPivotLoaded('organization_user', function () {
                return $this->pivot->role; // @phpstan-ignore-line
            }),
            'team_role' => $this->whenPivotLoaded('team_user', function () {
                return $this->pivot->role; // @phpstan-ignore-line
            }),
        ];
    }
}
