<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateTicket
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __invoke(User $creator, Project $project, array $input): Ticket
    {
        Validator::make($input, $this->rules())->validate();

        return DB::transaction(function () use ($project, $input) {
            /** @var Ticket $ticket */
            $ticket = $project->tickets()->create([
                'title' => $input['title'],
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? TicketStatus::Open,
                'display_order' => $input['display_order'] ?? 0,
            ]);

            // Assign creator as assignee if requested, or maybe just log it?
            // For now, let's say we might want to automatically assign the creator.
            if (isset($input['assignee_id'])) {
                $ticket->assignees()->attach($input['assignee_id'], ['type' => TicketUserType::Assignee]);
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
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
            'display_order' => ['sometimes', 'integer'],
        ];
    }
}
