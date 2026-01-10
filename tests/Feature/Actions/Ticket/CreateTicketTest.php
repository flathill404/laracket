<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\CreateTicket;
use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_ticket(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $action = new CreateTicket;

        $input = [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'assignee_id' => $user->id,
        ];

        $ticket = $action->create($user, $project, $input);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'status' => TicketStatus::Open,
        ]);

        $this->assertDatabaseHas('ticket_user', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => TicketUserType::Assignee,
        ]);
    }

    public function test_it_validates_ticket_creation(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $action = new CreateTicket;

        $this->assertThrows(function () use ($action, $user, $project) {
            $action->create($user, $project, [
                'title' => '',
            ]);
        }, ValidationException::class);

        $this->assertThrows(function () use ($action, $user, $project) {
            $action->create($user, $project, [
                'title' => str_repeat('a', 101),
            ]);
        }, ValidationException::class);
    }
}
