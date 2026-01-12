<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Team
 */
class TeamResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'members' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
