<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\UpdateTicketStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTicketStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_ticket_status(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $action = new UpdateTicketStatus;

        $updatedTicket = $action->update($user, $ticket, TicketStatus::InProgress);

        $this->assertEquals(TicketStatus::InProgress, $updatedTicket->status);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::InProgress,
        ]);
    }
}
