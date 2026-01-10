<?php

use App\Actions\Ticket\UpdateTicketStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates ticket status', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
    $action = new UpdateTicketStatus;

    $updatedTicket = $action->update($ticket, TicketStatus::InProgress);

    $this->assertEquals(TicketStatus::InProgress, $updatedTicket->status);
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => TicketStatus::InProgress,
    ]);
});
