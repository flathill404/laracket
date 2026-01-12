<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ticket
 */
class TicketResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project_id' => $this->project_id,
            'deadline' => $this->deadline, // @phpstan-ignore-line
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assignees' => UserResource::collection($this->whenLoaded('assignees')),
            'reviewers' => UserResource::collection($this->whenLoaded('reviewers')),
        ];
    }
}
