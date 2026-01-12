<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $myOrg = Organization::factory()->create([
            'name' => 'The Strongest Devil Organization',
        ]);

        $me = User::factory()->create([
            'name' => 'Power-chan',
            'email' => 'power@example.com',
            'password' => bcrypt('password'),
        ]);

        $myOrg->users()->attach($me->id, ['role' => \App\Enums\OrganizationRole::Owner]);

        $members = User::factory(10)->create();

        $myOrg->users()->attach($members->pluck('id'), ['role' => \App\Enums\OrganizationRole::Member]);

        $teamA = Team::factory()->create([
            'organization_id' => $myOrg->id,
            'name' => 'Development Team',
        ]);

        $teamA->users()->attach($me->id, ['role' => \App\Enums\TeamRole::Leader]);
        $teamA->users()->attach($members->random(3)->pluck('id'), ['role' => \App\Enums\TeamRole::Member]);

        $teamB = Team::factory()->create([
            'organization_id' => $myOrg->id,
            'name' => 'Design Team',
        ]);

        $teamBLeader = $members->random();

        $potentialMembers = $members->where('id', '!=', $teamBLeader->id);

        $teamB->users()->attach($teamBLeader->id, ['role' => \App\Enums\TeamRole::Leader]);
        $teamB->users()->attach($potentialMembers->random(3)->pluck('id'), ['role' => \App\Enums\TeamRole::Member]);

        $projects = Project::factory(3)->create([
            'organization_id' => $myOrg->id,
        ]);

        foreach ($projects as $project) {
            $project->assignedTeams()->attach($teamA->id);

            $project->assignedUsers()->attach($me->id);

            $tickets = Ticket::factory(10)->create([
                'project_id' => $project->id,
            ]);

            foreach ($tickets as $ticket) {
                $assignee = $members->random();
                $ticket->assignees()->attach($assignee->id, ['type' => \App\Enums\TicketUserType::Assignee]);

                $ticket->reviewers()->attach($me->id, ['type' => \App\Enums\TicketUserType::Reviewer]);

                // Comment seeding using correct relation
                \App\Models\Comment::factory(3)->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $members->random()->id,
                ]);
            }
        }

        $rivalOrg = Organization::factory()->create(['name' => 'Rival Organization']);
        $rivalUsers = User::factory(3)->create();
        $rivalOrg->users()->attach($rivalUsers->pluck('id'), ['role' => \App\Enums\OrganizationRole::Member]);
    }
}
