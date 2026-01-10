<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\UpdateTicketStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTicketStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_ticket_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $action = new UpdateTicketStatus;

        $updatedTicket = $action->update($ticket, TicketStatus::InProgress);

        $this->assertEquals(TicketStatus::InProgress, $updatedTicket->status);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::InProgress,
        ]);
    }
}
