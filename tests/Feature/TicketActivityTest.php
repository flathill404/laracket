<?php

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('logs activity when ticket is created', function () {
    $user = User::factory()->create();

    actingAs($user);
    $ticket = Ticket::factory()->create();

    assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => 'created',
        'payload' => null,
    ]);
});

it('logs activity when ticket is updated', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['title' => 'Old Title', 'status' => 'open']);

    actingAs($user);

    $ticket->update([
        'title' => 'New Title',
        'status' => 'in_progress',
    ]);

    assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => 'updated',
    ]);

    $activity = $ticket->activities()->where('type', 'updated')->first();

    expect($activity->payload)->toBe([
        'title' => ['from' => 'Old Title', 'to' => 'New Title'],
        'status' => ['from' => 'open', 'to' => 'in_progress'],
    ]);
});

it('does not log activity when ticket is updated without changes', function () {
    $user = User::factory()->create();
    actingAs($user);
    $ticket = Ticket::factory()->create(['title' => 'Old Title']);

    $ticket->update(['title' => 'Old Title']);

    // Should only have 'created' activity from factory if factory runs observers?
    // Factories usually fire events, so yes.
    // The previous test creates a ticket, so it has 1 activity (created).
    // Updating with same value -> should effectively do nothing or trigger update event with no dirty attributes.
    // Eloquent update() with same values often doesn't even fire events if nothing is dirty.
    // Let's force a "touch" or similar if we wanted to test filter, but basically if dirty is empty we return.

    // We expect count to be 1 (creation only)
    expect($ticket->activities()->count())->toBe(1);
    expect($ticket->activities()->first()->type)->toBe('created');
});

it('logs activity when comment is posted', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    actingAs($user);

    Comment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'body' => 'This is a comment',
    ]);

    assertDatabaseHas('ticket_activities', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'type' => 'commented',
    ]);

    $activity = $ticket->activities()->where('type', 'commented')->first();
    expect($activity->payload)->toBe(['body' => 'This is a comment']);
});
