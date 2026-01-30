<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateTicket
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(User $creator, Project $project, array $input): Ticket
    {
        /** @var array<string, mixed> $validated */
        $validated = Validator::make($input, $this->rules())->validate();

        if (isset($validated['assignee_id']) && is_string($validated['assignee_id'])) {
            $this->validateAssigneeIsProjectMember($project, $validated['assignee_id']);
        }

        $ticket = DB::transaction(function () use ($project, $validated, $creator) {
            /** @var Ticket $ticket */
            $ticket = $project->tickets()->create([
                'user_id' => $creator->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? TicketStatus::Open->value,
                'due_date' => $validated['due_date'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
            ]);

            if (isset($validated['assignee_id'])) {
                $ticket->assignees()->attach($validated['assignee_id'], ['type' => TicketUserType::Assignee->value]);
            }

            return $ticket;
        });

        return $ticket;
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
            'due_date' => ['nullable', 'date'],
            'display_order' => ['sometimes', 'integer'],
            'assignee_id' => ['sometimes', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * Check if the assigned user is a member of the project
     *
     * @throws ValidationException
     */
    protected function validateAssigneeIsProjectMember(Project $project, string $userId): void
    {
        $user = User::find($userId);

        if (! $user || ! $project->hasMember($user)) {
            throw ValidationException::withMessages([
                'assignee_id' => ['The assigned user is not a member of the project.'],
            ]);
        }
    }
}
