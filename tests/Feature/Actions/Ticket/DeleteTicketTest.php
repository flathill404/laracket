<?php

use App\Actions\Ticket\DeleteTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(RefreshDatabase::class);

it('deletes a ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();
    $action = new DeleteTicket;

    $action->delete($user, $ticket);

    assertModelMissing($ticket);
});
