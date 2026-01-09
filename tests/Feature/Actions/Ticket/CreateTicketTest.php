<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\CreateTicket;
use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_ticket(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $action = new CreateTicket;

        $data = [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'assignee_id' => $user->id,
        ];

        $ticket = $action->execute($user, $project, $data);

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
}
