<?php

use App\Actions\Ticket\UpdateTicket;
use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates a ticket', function () {
    $ticket = Ticket::factory()->create();
    $assignee = User::factory()->create();
    $reviewer = User::factory()->create();

    $action = new UpdateTicket;

    $input = [
        'title' => 'Updated Title',
        'assignees' => [$assignee->id],
        'reviewers' => [$reviewer->id],
    ];

    $updatedTicket = $action->update($ticket, $input);

    $this->assertEquals('Updated Title', $updatedTicket->title);

    $this->assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $assignee->id,
        'type' => TicketUserType::Assignee,
    ]);

    $this->assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $reviewer->id,
        'type' => TicketUserType::Reviewer,
    ]);
});

it('validates ticket update', function () {
    $ticket = Ticket::factory()->create();
    $action = new UpdateTicket;

    expect(fn () => $action->update($ticket, [
        'title' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
