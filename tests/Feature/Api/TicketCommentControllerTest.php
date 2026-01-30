<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(LazilyRefreshDatabase::class);

describe('TicketCommentController', function () {
    describe('index', function () {
        it('lists comments for a ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);

            // Grant access
            $project->assignedUsers()->attach($user);

            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            $otherUser = User::factory()->create();
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $otherUser->id,
                'body' => 'First comment',
                'created_at' => now()->subMinute(),
            ]);

            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => 'Second comment',
                'created_at' => now(),
            ]);

            getJson("/api/tickets/{$ticket->id}/comments")
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'body', 'created_at', 'user' => ['id', 'slug', 'name']],
                    ],
                ]);
        });

        it('denies access if not a member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            getJson("/api/tickets/{$ticket->id}/comments")
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('creates a comment', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);

            // Grant access
            $project->assignedUsers()->attach($user);

            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            $data = ['body' => 'My new comment'];

            postJson("/api/tickets/{$ticket->id}/comments", $data)
                ->assertCreated()
                ->assertJsonFragment(['body' => 'My new comment'])
                ->assertJsonStructure(['id', 'body', 'created_at', 'user']);

            assertDatabaseHas('comments', [
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => 'My new comment',
            ]);
        });

        it('validates input', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $project->assignedUsers()->attach($user);
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            postJson("/api/tickets/{$ticket->id}/comments", [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['body']);
        });

        it('denies creation if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $ticket = Ticket::factory()->create(['project_id' => $project->id]);

            postJson("/api/tickets/{$ticket->id}/comments", ['body' => 'foo'])
                ->assertForbidden();
        });
    });
});
