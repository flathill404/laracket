<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('UserTicketsController', function () {
    describe('index', function () {
        it('allows user to list tickets filtered by status', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $project = Project::factory()->for($organization)->create();

            // Assign user to project
            $project->assignedUsers()->attach($user);

            // Tickets
            $ticketOpen = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Open]);
            $ticketInProgress = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::InProgress]);
            $ticketClosed = Ticket::factory()->for($project)->create(['status' => \App\Enums\TicketStatus::Closed]);

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/tickets?status=open");

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $ticketOpen->id);

            $responseMultiple = getJson("/api/users/{$user->id}/tickets?status[]=open&status[]=in_progress");

            $responseMultiple->assertOk()
                ->assertJsonCount(2, 'data');

            $ids = collect($responseMultiple->json('data'))->pluck('id');
            expect($ids->contains($ticketOpen->id))->toBeTrue();
            expect($ids->contains($ticketInProgress->id))->toBeTrue();
            expect($ids->contains($ticketClosed->id))->toBeFalse();
        });

        it('allows user to list assigned project tickets', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $project = Project::factory()->for($organization)->create();

            // Assign user to project
            $project->assignedUsers()->attach($user);

            // Tickets in the project
            $ticket1 = Ticket::factory()->for($project)->create();
            $ticket2 = Ticket::factory()->for($project)->create();

            // Another project ticket (not assigned)
            $otherProject = Project::factory()->for($organization)->create();
            $otherTicket = Ticket::factory()->for($otherProject)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/tickets");

            $response->assertOk()
                ->assertJsonCount(2, 'data');

            $ids = collect($response->json('data'))->pluck('id');
            expect($ids->contains($ticket1->id))->toBeTrue();
            expect($ids->contains($ticket2->id))->toBeTrue();
            expect($ids->contains($otherTicket->id))->toBeFalse();
        });

        it('allows user to list team assigned project tickets', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $project = Project::factory()->for($organization)->create();
            $team = Team::factory()->for($organization)->create();

            // Assign user to team
            $team->members()->attach($user, ['role' => \App\Enums\TeamRole::Member]);

            // Assign team to project
            $project->assignedTeams()->attach($team);

            $ticket = Ticket::factory()->for($project)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/tickets");

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $ticket->id);
        });

        it('allows organization owner to list all tickets in organization', function () {
            $user = User::factory()->create();
            $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
            $project = Project::factory()->for($organization)->create();

            $ticket = Ticket::factory()->for($project)->create();

            // Another organization ticket
            $otherOrg = Organization::factory()->create();
            $otherProject = Project::factory()->for($otherOrg)->create();
            $otherTicket = Ticket::factory()->for($otherProject)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/tickets");

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $ticket->id);
        });

        it('allows organization admin to list all tickets in organization', function () {
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->for($organization)->create();
            $ticket = Ticket::factory()->for($project)->create();

            actingAs($user);

            $response = getJson("/api/users/{$user->id}/tickets");

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $ticket->id);
        });

        it('denies viewing other users tickets', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();

            actingAs($user);

            $response = getJson("/api/users/{$otherUser->id}/tickets");

            $response->assertForbidden();
        });
    });
});
