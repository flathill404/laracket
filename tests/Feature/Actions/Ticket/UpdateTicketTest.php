<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\UpdateTicket;
use App\Enums\TicketUserType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $assignee = User::factory()->create();
        $reviewer = User::factory()->create();

        $action = new UpdateTicket;

        $data = [
            'title' => 'Updated Title',
            'assignees' => [$assignee->id],
            'reviewers' => [$reviewer->id],
        ];

        $updatedTicket = $action->update($user, $ticket, $data);

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
    }
}
