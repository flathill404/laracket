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
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create the main development organization
        $myOrg = Organization::factory()->create([
            'name' => 'The Strongest Devil Organization',
        ]);

        // 2. Create a login account for myself (Representative)
        $me = User::factory()->create([
            'name' => 'Power-chan',
            'email' => 'power@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $myOrg->id,
            'is_representative' => true,
        ]);

        // 3. Create 10 other members (same organization)
        $members = User::factory(10)->create([
            'organization_id' => $myOrg->id,
        ]);

        // 4. Create teams and assign members

        // --- Team A: Myself (Leader) + 3 members ---
        // $me is separate from $members, so no conflict here.
        $teamA = Team::factory()->create([
            'organization_id' => $myOrg->id,
            'name' => 'Development Team',
        ]);
        $teamA->users()->attach($me->id, ['role' => 'leader']);
        $teamA->users()->attach($members->random(3)->pluck('id'), ['role' => 'member']);

        // --- Team B: 1 leader from members + 3 members ---
        $teamB = Team::factory()->create([
            'organization_id' => $myOrg->id,
            'name' => 'Design Team',
        ]);

        // FIX: Pick a leader first, then exclude them from member selection
        $teamBLeader = $members->random();
        // Exclude the leader from the potential members list
        $potentialMembers = $members->where('id', '!=', $teamBLeader->id);

        $teamB->users()->attach($teamBLeader->id, ['role' => 'leader']);
        // Pick 3 from the REMAINING members
        $teamB->users()->attach($potentialMembers->random(3)->pluck('id'), ['role' => 'member']);

        // 5. Create projects (associated with the organization)
        $projects = Project::factory(3)->create([
            'organization_id' => $myOrg->id,
        ]);

        // 6. Assign to projects and generate tickets
        foreach ($projects as $project) {
            // Assign Team A to the project
            $project->assignedTeams()->attach($teamA->id);
            // Assign myself individually as well (for verification)
            $project->assignedUsers()->attach($me->id);

            // Create 10 tickets
            $tickets = Ticket::factory(10)->create([
                'project_id' => $project->id,
            ]);

            // Randomly assign assignees and reviewers to each ticket
            foreach ($tickets as $ticket) {
                // Assignee (From the members)
                $assignee = $members->random();
                $ticket->assignees()->attach($assignee->id, ['role' => 'assignee']);

                // Reviewer (Set to myself)
                $ticket->reviewers()->attach($me->id, ['role' => 'reviewer']);
            }
        }

        // Just in case, create some data for another organization (for multi-tenant testing)
        Organization::factory()
            ->has(User::factory()->count(3))
            ->create(['name' => 'Rival Organization']);
    }
}
