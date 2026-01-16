<?php

namespace Database\Seeders;

use App\Actions\Organization\CreateOrganization;
use App\Enums\OrganizationRole;
use App\Enums\TeamRole;
use App\Enums\TicketStatus;
use App\Enums\TicketUserType;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $yamlPath = database_path('seeders/demo.yaml');

        if (! file_exists($yamlPath)) {
            $this->command->error("File not found: $yamlPath");

            return;
        }

        $data = \Symfony\Component\Yaml\Yaml::parseFile($yamlPath);

        // 1. Users (Jeison First)
        $users = [];

        // 1.1 Create Jeison manually to ensure ID 1
        $jeison = User::factory()->create([
            'name' => 'jeison',
            'display_name' => 'Jeison Stethem',
            'email' => 'jeison.stethem@acme.com',
            'password' => bcrypt('password'),
        ]);
        $users['jeison'] = $jeison;

        // 1.2 Create other users from YAML
        foreach ($data['users'] ?? [] as $userData) {
            if ($userData['email'] === 'jeison.stethem@acme.com') {
                continue;
            }

            $user = User::factory()->create([
                'name' => $userData['name'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'),
            ]);
            $users[$userData['name']] = $user;
        }

        // 2. Personal Projects (Actions)
        $createOrganization = app(CreateOrganization::class);
        foreach ($users as $user) {
            $org = $createOrganization($user, [
                'name' => 'the-'.$user->name.'-project',
                'display_name' => 'The '.$user->display_name.' Project',
            ]);
        }

        // 3. Organizations (YAML)
        $organizations = [];
        foreach ($data['organizations'] ?? [] as $orgData) {
            $organizations[$orgData['name']] = Organization::factory()->create([
                'name' => $orgData['name'],
                'display_name' => $orgData['display_name'],
            ]);
        }

        // 4. Teams
        $teams = [];
        foreach ($data['teams'] ?? [] as $teamData) {
            if (! isset($organizations[$teamData['organization_name']])) {
                continue;
            }
            $teams[$teamData['name']] = Team::factory()->create([
                'organization_id' => $organizations[$teamData['organization_name']]->id,
                'name' => $teamData['name'],
                'display_name' => $teamData['display_name'],
            ]);
        }

        // 5. Projects (YAML)
        $projects = [];
        foreach ($data['projects'] ?? [] as $projectData) {
            if (! isset($organizations[$projectData['organization_name']])) {
                continue;
            }
            $projects[$projectData['name']] = Project::factory()->create([
                'organization_id' => $organizations[$projectData['organization_name']]->id,
                'name' => $projectData['name'],
                'display_name' => $projectData['display_name'],
            ]);
        }

        // Link Projects to Teams (All teams in Org get access to all projects in Org for demo)
        foreach ($projects as $project) {
            $orgTeams = $project->organization->teams;
            $project->assignedTeams()->sync($orgTeams);
        }

        // 6. Attach Users to Orgs and Teams
        foreach ($data['users'] ?? [] as $userData) {
            if (! isset($users[$userData['name']])) {
                continue;
            }
            $user = $users[$userData['name']];
            $orgName = $userData['organization_name'];

            if (isset($organizations[$orgName])) {
                // Check if already attached (CreateOrganization might have attached creator to something, but here we attach to ACME)
                if (! $organizations[$orgName]->users()->where('user_id', $user->id)->exists()) {
                    $organizations[$orgName]->users()->attach($user->id, ['role' => OrganizationRole::Member]);
                }
            }

            foreach ($userData['teams'] ?? [] as $teamName) {
                if (isset($teams[$teamName])) {
                    $teams[$teamName]->users()->attach($user->id, ['role' => TeamRole::Member]);
                }
            }
        }

        // 7. Tickets
        foreach ($data['tickets'] ?? [] as $ticketData) {
            if (! isset($projects[$ticketData['project_name']])) {
                continue;
            }

            $project = $projects[$ticketData['project_name']];
            $status = TicketStatus::from($ticketData['status']);

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'status' => $status,
            ]);

            if (isset($ticketData['assignee_name']) && isset($users[$ticketData['assignee_name']])) {
                $ticket->assignees()->attach($users[$ticketData['assignee_name']]->id, ['type' => TicketUserType::Assignee]);
            }

            if (isset($ticketData['reviewer_name']) && isset($users[$ticketData['reviewer_name']])) {
                $ticket->reviewers()->attach($users[$ticketData['reviewer_name']]->id, ['type' => TicketUserType::Reviewer]);
            }

            foreach ($ticketData['comments'] ?? [] as $commentData) {
                if (isset($users[$commentData['user_name']])) {
                    \App\Models\Comment::factory()->create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $users[$commentData['user_name']]->id,
                        'body' => $commentData['body'],
                    ]);
                }
            }
        }
    }
}
