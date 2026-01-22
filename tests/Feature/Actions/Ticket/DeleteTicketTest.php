<?php

declare(strict_types=1);

use App\Actions\Ticket\DeleteTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\assertModelMissing;

uses(LazilyRefreshDatabase::class);

it('deletes a ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();
    $action = new DeleteTicket;

    $action($user, $ticket);

    assertModelMissing($ticket);
});
