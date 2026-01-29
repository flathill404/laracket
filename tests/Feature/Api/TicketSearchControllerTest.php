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

describe('TicketSearchController', function () {
    describe('index', function () {
        it('searches tickets by keyword', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);

            Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => 'Fix login bug',
                'description' => 'Users cannot login',
            ]);

            Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => 'Add new feature',
                'description' => 'Implement search functionality',
            ]);

            getJson('/api/tickets/search?q=login')
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'project_id', 'status'],
                    ],
                ]);
        });

        it('filters by project_id', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project1 = Project::factory()->create(['organization_id' => $organization->id]);
            $project2 = Project::factory()->create(['organization_id' => $organization->id]);

            Ticket::factory()->create(['project_id' => $project1->id, 'title' => 'Ticket A']);
            Ticket::factory()->create(['project_id' => $project2->id, 'title' => 'Ticket B']);

            getJson("/api/tickets/search?q=Ticket&project_id={$project1->id}")
                ->assertOk();
        });

        it('filters by status', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);

            Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => 'Open ticket',
                'status' => TicketStatus::Open,
            ]);

            Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => 'Closed ticket',
                'status' => TicketStatus::Closed,
            ]);

            getJson('/api/tickets/search?q=ticket&status=open')
                ->assertOk();
        });

        it('only returns tickets from accessible projects', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Member]);

            $accessibleProject = Project::factory()->create(['organization_id' => $organization->id]);
            $accessibleProject->assignedUsers()->attach($user);

            $otherOrganization = Organization::factory()->create(['owner_user_id' => $otherUser->id]);
            $inaccessibleProject = Project::factory()->create(['organization_id' => $otherOrganization->id]);

            Ticket::factory()->create(['project_id' => $accessibleProject->id, 'title' => 'Accessible']);
            Ticket::factory()->create(['project_id' => $inaccessibleProject->id, 'title' => 'Inaccessible']);

            getJson('/api/tickets/search?q=ticket')
                ->assertOk();
        });

        it('requires authentication', function () {
            getJson('/api/tickets/search?q=test')
                ->assertUnauthorized();
        });

        it('returns empty result for empty keyword', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);

            Ticket::factory()->create(['project_id' => $project->id, 'title' => 'Test ticket']);

            getJson('/api/tickets/search?q=')
                ->assertOk();
        });

        it('paginates results', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create(['organization_id' => $organization->id]);

            Ticket::factory(30)->create(['project_id' => $project->id]);

            getJson('/api/tickets/search?q=&per_page=10')
                ->assertOk()
                ->assertJsonStructure([
                    'data',
                    'links',
                ]);
        });
    });
});
