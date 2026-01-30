<?php

declare(strict_types=1);

use App\Enums\TicketActivityType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Values\ActivityPayload;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('TicketActivityController', function () {
    describe('index', function () {
        it('lists activities for a ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);

            $project->assignedUsers()->attach($user);

            // Observer creates 'created' activity when ticket is created
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => TicketActivityType::Updated,
                'payload' => new ActivityPayload(['status' => ['from' => 'open', 'to' => 'in_progress']]),
                'created_at' => now(),
            ]);

            // 1 from observer + 1 manually created = 2 activities
            getJson("/api/tickets/{$ticket->id}/activities")
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'type', 'payload', 'created_at', 'user' => ['id', 'slug', 'name']],
                    ],
                ]);
        });

        it('returns activities in ascending order by created_at', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);

            $project->assignedUsers()->attach($user);

            // Observer creates 'created' activity when ticket is created
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => TicketActivityType::Updated,
                'payload' => new ActivityPayload(['status' => ['from' => 'open', 'to' => 'in_progress']]),
                'created_at' => now()->addMinute(),
            ]);

            // 1 from observer + 1 manually created = 2 activities
            $response = getJson("/api/tickets/{$ticket->id}/activities")
                ->assertOk()
                ->assertJsonCount(2, 'data');

            $data = $response->json('data');
            // Oldest first (ascending order)
            expect($data[0]['type'])->toBe('created');
            expect($data[1]['type'])->toBe('updated');
        });

        it('denies access if not a member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            getJson("/api/tickets/{$ticket->id}/activities")
                ->assertForbidden();
        });
    });
});
