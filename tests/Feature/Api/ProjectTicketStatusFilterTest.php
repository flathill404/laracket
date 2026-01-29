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
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('ProjectTicketStatusFilter', function () {
    describe('index with status filter', function () {
        it('filters tickets by single status', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            $openTicket = Ticket::factory()->create([
                'project_id' => $project->id,
                'status' => TicketStatus::Open,
            ]);

            $closedTicket = Ticket::factory()->create([
                'project_id' => $project->id,
                'status' => TicketStatus::Closed,
            ]);

            getJson("/api/projects/{$project->id}/tickets?status=".TicketStatus::Open->value)
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $openTicket->id])
                ->assertJsonMissing(['id' => $closedTicket->id]);
        });

        it('filters tickets by multiple statuses', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            $openTicket = Ticket::factory()->create([
                'project_id' => $project->id,
                'status' => TicketStatus::Open,
            ]);

            $inProgressTicket = Ticket::factory()->create([
                'project_id' => $project->id,
                'status' => TicketStatus::InProgress,
            ]);

            $closedTicket = Ticket::factory()->create([
                'project_id' => $project->id,
                'status' => TicketStatus::Closed,
            ]);

            $url = "/api/projects/{$project->id}/tickets?status[]=".TicketStatus::Open->value.'&status[]='.TicketStatus::InProgress->value;

            getJson($url)
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonFragment(['id' => $openTicket->id])
                ->assertJsonFragment(['id' => $inProgressTicket->id])
                ->assertJsonMissing(['id' => $closedTicket->id]);
        });

        it('returns all tickets when no status filter is provided', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $project->assignedUsers()->attach($user);

            Ticket::factory()->create(['project_id' => $project->id, 'status' => TicketStatus::Open]);
            Ticket::factory()->create(['project_id' => $project->id, 'status' => TicketStatus::Closed]);

            getJson("/api/projects/{$project->id}/tickets")
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });
    });
});
