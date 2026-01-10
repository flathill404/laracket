<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateTicket
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __invoke(Ticket $ticket, array $input): Ticket
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($ticket, $input) {
            $ticket->update([
                'title' => $input['title'] ?? $ticket->title,
                'description' => $input['description'] ?? $ticket->description,
                'display_order' => $input['display_order'] ?? $ticket->display_order,
                'status' => $input['status'] ?? $ticket->status,
            ]);

            if (isset($input['assignees'])) {
                // Sync assignees, expecting array of user IDs
                $ticket->assignees()->syncWithPivotValues($input['assignees'], ['type' => TicketUserType::Assignee]);
            }

            if (isset($input['reviewers'])) {
                // Sync reviewers
                $ticket->reviewers()->syncWithPivotValues($input['reviewers'], ['type' => TicketUserType::Reviewer]);
            }

            return $ticket;
        });
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'display_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
        ];
    }
}
