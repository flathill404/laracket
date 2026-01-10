<?php

use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ticket policy', function () {
    $owner = User::factory()->create();
    $reviewer = User::factory()->create();
    $assignee = User::factory()->create();
    $stranger = User::factory()->create();

    $org = Organization::factory()->create(['owner_user_id' => $owner->id]);
    $project = Project::factory()->create(['organization_id' => $org->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    // Setup access for reviewer/assignee to project to pass 'view' check
    $project->assignedUsers()->attach($reviewer);
    $project->assignedUsers()->attach($assignee);

    $ticket->reviewers()->attach($reviewer, ['type' => TicketUserType::Reviewer]);
    $ticket->assignees()->attach($assignee, ['type' => TicketUserType::Assignee]);

    $policy = new TicketPolicy;

    // View
    expect($policy->view($assignee, $ticket))->toBeTrue();
    expect($policy->view($stranger, $ticket))->toBeFalse();

    // Delete
    expect($policy->delete($owner, $ticket))->toBeTrue();
    expect($policy->delete($reviewer, $ticket))->toBeTrue();
    expect($policy->delete($assignee, $ticket))->toBeFalse(); // Assignee can't delete unless they are reviewer or admin
});
