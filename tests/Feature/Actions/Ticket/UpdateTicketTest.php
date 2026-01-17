<?php

use App\Actions\Ticket\UpdateTicket;
use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('updates a ticket', function () {
    $ticket = Ticket::factory()->create();
    $assignee = User::factory()->create();
    $reviewer = User::factory()->create();

    $action = new UpdateTicket;

    $input = [
        'title' => 'Updated Title',
        'due_date' => '2026-12-31',
        'assignees' => [$assignee->id],
        'reviewers' => [$reviewer->id],
    ];

    $updatedTicket = $action($ticket, $input);

    expect($updatedTicket->title)->toBe('Updated Title');
    expect($updatedTicket->due_date->format('Y-m-d'))->toBe('2026-12-31');

    assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $assignee->id,
        'type' => TicketUserType::Assignee,
    ]);

    assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $reviewer->id,
        'type' => TicketUserType::Reviewer,
    ]);
});

it('validates ticket update', function () {
    $ticket = Ticket::factory()->create();
    $action = new UpdateTicket;

    expect(fn () => $action($ticket, [
        'title' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
