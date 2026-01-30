<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Enums\TicketStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(LazilyRefreshDatabase::class);

describe('TicketController', function () {
    describe('index', function () {
        it('lists tickets in project', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            // User needs access to project
            $project->assignedUsers()->attach($user);

            $tickets = Ticket::factory(3)->create([
                'project_id' => $project->id,
            ]);

            getJson("/api/projects/{$project->id}/tickets")
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'project_id', 'status'],
                    ],
                ]);
        });

        it('can paginate and sort tickets', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $project->assignedUsers()->attach($user);

            // Create tickets with specific dates
            $ticket1 = Ticket::factory()->for($project)->create(['created_at' => now()->subDays(3)]);
            $ticket2 = Ticket::factory()->for($project)->create(['created_at' => now()->subDays(1)]);
            $ticket3 = Ticket::factory()->for($project)->create(['created_at' => now()->subDays(2)]);

            // Sort asc
            $responseAsc = getJson("/api/projects/{$project->id}/tickets?sort=created_at&per_page=10");
            $responseAsc->assertOk();
            $idsAsc = collect($responseAsc->json('data'))->pluck('id');
            $this->assertEquals([$ticket1->id, $ticket3->id, $ticket2->id], $idsAsc->toArray());

            // Sort desc
            $responseDesc = getJson("/api/projects/{$project->id}/tickets?sort=-created_at&per_page=10");
            $responseDesc->assertOk();
            $idsDesc = collect($responseDesc->json('data'))->pluck('id');
            $this->assertEquals([$ticket2->id, $ticket3->id, $ticket1->id], $idsDesc->toArray());

            // Pagination
            $responsePage = getJson("/api/projects/{$project->id}/tickets?sort=id&per_page=2");
            $responsePage->assertOk()->assertJsonCount(2, 'data');
            $this->assertArrayHasKey('meta', $responsePage->json());
            $this->assertArrayHasKey('links', $responsePage->json());
        });

        it('denies access if not a member', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            // User not attached

            getJson("/api/projects/{$project->id}/tickets")
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('creates a ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            $data = [
                'title' => 'New Ticket',
                'description' => 'Ticket Description',
                'priority' => 'high', // Assuming priority field exists or is ignored safely
            ];

            postJson("/api/projects/{$project->id}/tickets", $data)
                ->assertCreated()
                ->assertJsonFragment([
                    'title' => 'New Ticket',
                    'project_id' => $project->id,
                    'status' => TicketStatus::Open->value,
                ]);

            assertDatabaseHas('tickets', [
                'title' => 'New Ticket',
                'project_id' => $project->id,
            ]);
        });

        it('denies creation if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            // User not authorized

            $data = [
                'title' => 'New Ticket',
            ];

            postJson("/api/projects/{$project->id}/tickets", $data)
                ->assertForbidden();
        });

        it('validates input', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            postJson("/api/projects/{$project->id}/tickets", [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('sets the creator of the ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);
            $project->assignedUsers()->attach($user);

            $data = [
                'title' => 'Ticket with Creator',
            ];

            postJson("/api/projects/{$project->id}/tickets", $data)->assertCreated();

            assertDatabaseHas('tickets', [
                'title' => 'Ticket with Creator',
                'user_id' => $user->id,
            ]);
        });
    });

    describe('show', function () {
        it('shows ticket details', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
            ]);

            getJson("/api/tickets/{$ticket->id}")
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                    ],
                ]);
        });

        it('denies access if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
            ]);
            // User not authorized

            getJson("/api/tickets/{$ticket->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('updates ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => 'Old Title',
            ]);

            $data = [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ];

            putJson("/api/tickets/{$ticket->id}", $data)
                ->assertOk()
                ->assertJsonFragment([
                    'title' => 'Updated Title',
                ]);

            assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'title' => 'Updated Title',
            ]);
        });

        it('denies update if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
            ]);
            // User not authorized

            putJson("/api/tickets/{$ticket->id}", ['title' => 'New Title'])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes ticket', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
            ]);

            deleteJson("/api/tickets/{$ticket->id}")
                ->assertNoContent();

            assertDatabaseMissing('tickets', [
                'id' => $ticket->id,
            ]);
        });

        it('denies delete if not authorized', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
            ]);
            // Member cannot delete unless creator/reviewer logic allows, assuming default policy

            deleteJson("/api/tickets/{$ticket->id}")
                ->assertForbidden();
        });
    });
});
