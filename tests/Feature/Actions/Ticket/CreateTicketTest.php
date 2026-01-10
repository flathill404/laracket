<?php

use App\Actions\Ticket\CreateTicket;
use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('creates a ticket', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $action = new CreateTicket;

    $input = [
        'title' => 'Test Ticket',
        'description' => 'Test Description',
        'assignee_id' => $user->id,
    ];

    $ticket = $action->create($user, $project, $input);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Test Ticket',
        'description' => 'Test Description',
        'status' => TicketStatus::Open,
    ]);

    assertDatabaseHas('ticket_user', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => TicketUserType::Assignee,
    ]);
});

it('validates ticket creation', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $action = new CreateTicket;

    expect(fn () => $action->create($user, $project, [
        'title' => '',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action->create($user, $project, [
        'title' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
