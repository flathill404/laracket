<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateTicket
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function __invoke(Ticket $ticket, array $input): Ticket
    {
        $validated = Validator::make($input, $this->rules())->validate();

        DB::transaction(function () use ($ticket, $validated) {
            $attributes = Arr::except($validated, ['assignees', 'reviewers']);

            if (! empty($attributes)) {
                /** @var array<string, mixed> $attributes */
                $ticket->update($attributes);
            }

            if (isset($validated['assignees'])) {
                // Enumのvalueを渡すのが確実じゃ
                $ticket->assignees()->syncWithPivotValues((array) $validated['assignees'], ['type' => TicketUserType::Assignee->value]);
            }

            if (isset($validated['reviewers'])) {
                $ticket->reviewers()->syncWithPivotValues((array) $validated['reviewers'], ['type' => TicketUserType::Reviewer->value]);
            }
        });

        return $ticket;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'display_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
            'assignees' => ['sometimes', 'array'],
            'reviewers' => ['sometimes', 'array'],
        ];
    }
}
