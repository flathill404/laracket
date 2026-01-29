<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

uses(LazilyRefreshDatabase::class);

describe('TicketOrderController', function () {
    describe('update', function () {
        it('updates ticket order', function () {
            $user = User::factory()->create();
            actingAs($user);

            $organization = Organization::factory()->create();
            $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

            $project = Project::factory()->create([
                'organization_id' => $organization->id,
            ]);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
                'display_order' => 100,
            ]);

            patchJson("/api/tickets/{$ticket->id}/order", [
                'order' => 200.5,
            ])
                ->assertNoContent();

            assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'display_order' => 200.5,
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
                'display_order' => 100,
            ]);
            // User not authorized

            patchJson("/api/tickets/{$ticket->id}/order", [
                'order' => 200.5,
            ])
                ->assertForbidden();
        });
    });
});
