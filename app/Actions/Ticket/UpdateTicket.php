<?php

namespace App\Actions\Ticket;

use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTicket
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($actor, $ticket, $data) {
            $ticket->update([
                'title' => $data['title'] ?? $ticket->title,
                'description' => $data['description'] ?? $ticket->description,
                'display_order' => $data['display_order'] ?? $ticket->display_order,
            ]);

            if (isset($data['assignees'])) {
                // Sync assignees, expecting array of user IDs
                $ticket->assignees()->syncWithPivotValues($data['assignees'], ['type' => TicketUserType::Assignee]);
            }

            if (isset($data['reviewers'])) {
                 // Sync reviewers
                $ticket->reviewers()->syncWithPivotValues($data['reviewers'], ['type' => TicketUserType::Reviewer]);
            }

            return $ticket;
        });
    }
}
