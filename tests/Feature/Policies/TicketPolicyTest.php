<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows organization owner to view the ticket', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();

    expect($user->can('view', $ticket))->toBeTrue();
});

it('allows project member to view the ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('view', $ticket))->toBeTrue();
});

it('denies outsider to view the ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();

    expect($user->can('view', $ticket))->toBeFalse();
});

it('allows project member to create tickets', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('create', [Ticket::class, $project]))->toBeTrue();
});

it('allows project member to update the ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('update', $ticket))->toBeTrue();
});

it('allows organization owner to delete the ticket', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();

    expect($user->can('delete', $ticket))->toBeTrue();
});

it('allows reviewer to delete the ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);
    $ticket->reviewers()->attach($user, ['type' => App\Enums\TicketUserType::Reviewer]);

    expect($user->can('delete', $ticket))->toBeTrue();
});

it('denies regular project member to delete the ticket', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('delete', $ticket))->toBeFalse();
});

it('allows project member to assign user', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('assign_user', $ticket))->toBeTrue();
});

it('allows project member to unassign user', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->for($organization)->create();
    $ticket = Ticket::factory()->for($project)->create();
    $user = User::factory()->create();
    $project->members()->attach($user);

    expect($user->can('unassign_user', $ticket))->toBeTrue();
});
