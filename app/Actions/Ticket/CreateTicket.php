<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTicket
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $creator, Project $project, array $data): Ticket
    {
        return DB::transaction(function () use ($project, $data) {
            /** @var Ticket $ticket */
            $ticket = $project->tickets()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => TicketStatus::Open,
                'display_order' => $data['display_order'] ?? 0,
            ]);

            // Assign creator as assignee if requested, or maybe just log it?
            // For now, let's say we might want to automatically assign the creator.
            if (isset($data['assignee_id'])) {
                $ticket->assignees()->attach($data['assignee_id'], ['type' => TicketUserType::Assignee]);
            }

            return $ticket;
        });
    }
}
