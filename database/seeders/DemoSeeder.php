<?php

namespace Database\Seeders;

use App\Actions\Organization\CreateOrganization;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Enums\OrganizationRole;
use App\Enums\TeamRole;
use App\Enums\TicketUserType;
use App\Enums\TicketStatus;

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

        // 1. Organizations
        $organizations = [];
        foreach ($data['organizations'] ?? [] as $orgData) {
            $organizations[$orgData['name']] = Organization::factory()->create([
                'name' => $orgData['name'],
                'display_name' => $orgData['display_name'],
            ]);
        }

        // 2. Teams
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

        // 3. Projects
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

        // 4. Users
        $users = [];
        foreach ($data['users'] ?? [] as $userData) {
            $user = User::factory()->create([
                'name' => $userData['name'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'), // Explicitly set password for demo
            ]);
            $users[$userData['name']] = $user;

            $orgName = $userData['organization_name'];
            if (isset($organizations[$orgName])) {
                $organizations[$orgName]->users()->attach($user->id, ['role' => OrganizationRole::Member]);
            }

            foreach ($userData['teams'] ?? [] as $teamName) {
                if (isset($teams[$teamName])) {
                    $teams[$teamName]->users()->attach($user->id, ['role' => TeamRole::Member]);
                }
            }
        }

        // 4.1 Personal Projects
        $createOrganization = app(CreateOrganization::class);
        foreach ($users as $user) {
            $createOrganization($user, [
                'name' => 'the-'.$user->name.'-project',
                'display_name' => 'The '.$user->display_name.' Project',
            ]);
        }

        // 5. Tickets
        foreach ($data['tickets'] ?? [] as $ticketData) {
            if (! isset($projects[$ticketData['project_name']])) {
                continue;
            }

            $project = $projects[$ticketData['project_name']];

            // Normalize status string to case or lower
            // Assuming TicketStatus enum matching. If existing enum is PascalCase, we might need mapping.
            // Let's assume the string in YAML matches the Enum value or name case-insensitively.
            // The YAML has 'in_progress', 'resolved', 'open', 'closed', 'in_review'.
            // I need to check TicketStatus enum values. 
            // For now I'll try to find a matching backing value or case name.
            
            $status = $this->parseTicketStatus($ticketData['status']);

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

    private function parseTicketStatus(string $statusString): TicketStatus
    {
        // Try to match Enum value first
        $status = TicketStatus::tryFrom($statusString);
        if ($status) {
            return $status;
        }

        // Map YAML common strings to likely Enum cases if direct match fails
        // You might need to adjust this depending on TicketStatus definition
        return match($statusString) {
            'in_progress' => TicketStatus::InProgress,
            'in_review' => TicketStatus::InReview,
            'resolved' => TicketStatus::Resolved,
            'closed' => TicketStatus::Closed,
            default => TicketStatus::Open,
        };
    }
}
