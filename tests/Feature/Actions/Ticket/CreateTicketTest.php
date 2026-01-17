<?php

use App\Actions\Ticket\CreateTicket;
use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('creates a ticket', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $project->assignedUsers()->attach($user);
    $action = new CreateTicket;

    $input = [
        'title' => 'Test Ticket',
        'description' => 'Test Description',
        'assignee_id' => $user->id,
        'due_date' => '2026-01-01',
    ];

    $ticket = $action($user, $project, $input);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Test Ticket',
        'description' => 'Test Description',
        'status' => TicketStatus::Open,
        'due_date' => '2026-01-01 00:00:00',
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

    expect(fn () => $action($user, $project, [
        'title' => '',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action($user, $project, [
        'title' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
