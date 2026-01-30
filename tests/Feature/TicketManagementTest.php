<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(LazilyRefreshDatabase::class);

describe('Ticket Management', function () {
    it('generates sequential issue numbers on creation', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Ensure user can access project
        $project->organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        actingAs($user);

        $ticket1 = Ticket::factory()->create(['project_id' => $project->id]);
        $ticket2 = Ticket::factory()->create(['project_id' => $project->id]);
        $ticket3 = Ticket::factory()->create(['project_id' => $project->id]);

        expect($ticket1->issue_number)->toBe(1)
            ->and($ticket2->issue_number)->toBe(2)
            ->and($ticket3->issue_number)->toBe(3);
    });

    it('scopes issue numbers to project', function () {
        $user = User::factory()->create();
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();

        $projectA->organization->users()->attach($user, ['role' => OrganizationRole::Admin]);
        $projectB->organization->users()->attach($user, ['role' => OrganizationRole::Admin]);

        actingAs($user);

        $ticketA1 = Ticket::factory()->create(['project_id' => $projectA->id]);
        $ticketB1 = Ticket::factory()->create(['project_id' => $projectB->id]);
        $ticketA2 = Ticket::factory()->create(['project_id' => $projectA->id]);

        expect($ticketA1->issue_number)->toBe(1)
            ->and($ticketB1->issue_number)->toBe(1)
            ->and($ticketA2->issue_number)->toBe(2);
    });

    it('calculates full_id correctly', function () {
        $project = Project::factory()->create(['key' => 'WEB']);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'issue_number' => 101, // Override for test? Observer skips if not null!
        ]);

        expect($ticket->full_id)->toBe('WEB-101');
    });

    it('handles scoped routing correctly', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['slug' => 'web-project']);
        $project->organization->users()->attach($user, ['role' => OrganizationRole::Member]);
        $project->assignedUsers()->attach($user);

        actingAs($user);

        $ticket = Ticket::factory()->create(['project_id' => $project->id]);
        // issue_number should be 1

        // Access via new URL: /projects/{project}/tickets/{ticket}
        // Note: Project bound by slug (getRouteKeyName='slug'), Ticket by issue_number (getRouteKeyName='issue_number')
        // URL construction: /projects/{project_slug}/tickets/{issue_number}
        // Wait, current route in api.php is /organizations/{org}/projects/{project}/tickets/{ticket}

        $url = "/api/organizations/{$project->organization->id}/projects/{$project->slug}/tickets/{$ticket->issue_number}";

        getJson($url)
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.issue_number', 1);
    });

    it('denies access to ticket via wrong project URL', function () {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $projectA = Project::factory()->create(['organization_id' => $organization->id, 'slug' => 'proj-a']);
        $projectB = Project::factory()->create(['organization_id' => $organization->id, 'slug' => 'proj-b']);

        $organization->users()->attach($user, ['role' => OrganizationRole::Admin]);
        actingAs($user);

        $ticketA = Ticket::factory()->create(['project_id' => $projectA->id]); // #1
        // Project B has NO tickets.

        // Try to access ticket #1 using projectB's URL
        // If scoped correctly, it searches Project B for issue #1 -> Not found -> 404.
        // If NOT scoped, it might find Ticket A #1 (global search).

        $url = "/api/organizations/{$organization->id}/projects/{$projectB->slug}/tickets/{$ticketA->issue_number}";
        getJson($url)->assertNotFound();
    });
});
