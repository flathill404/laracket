<?php

namespace Tests\Feature\Actions\Ticket;

use App\Actions\Ticket\DeleteTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_a_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $action = new DeleteTicket;

        $action->execute($user, $ticket);

        $this->assertModelMissing($ticket);
    }
}
