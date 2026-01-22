<?php

declare(strict_types=1);

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
use Illuminate\Http\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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
        $jeisonData = [
            'name' => 'jeison',
            'display_name' => 'Jeison Stethem',
            'email' => 'jeison.stethem@acme.com',
            'password' => bcrypt('password'),
        ];

        $jeisonAvatarFile = database_path('seeders/imgaes/avatars/jeison.webp');
        if (file_exists($jeisonAvatarFile)) {
            $jeisonData['avatar_path'] = Storage::putFile('avatars', new File($jeisonAvatarFile));
        }

        $jeison = User::factory()->create($jeisonData);
        $users['jeison'] = $jeison;

        // 1.2 Create other users from YAML
        foreach ($data['users'] ?? [] as $userData) {
            if ($userData['email'] === 'jeison.stethem@acme.com') {
                continue;
            }

            $input = [
                'name' => $userData['name'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'),
            ];

            $avatarFile = database_path('seeders/imgaes/avatars/'.$userData['name'].'.webp');
            if (file_exists($avatarFile)) {
                $input['avatar_path'] = Storage::putFile('avatars', new File($avatarFile));
            }

            $user = User::factory()->create($input);
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

            // Generate due_date with random distribution
            $dueDate = $this->generateRandomDueDate();

            $ticket = Ticket::factory()->create([
                'project_id' => $project->id,
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'status' => $status,
                'due_date' => $dueDate,
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

        // 8. Build search index
        Artisan::call('scout:import', ['model' => Ticket::class]);
    }

    /**
     * Generate a random due date with realistic distribution.
     *
     * - ~20% chance of null (no due date)
     * - ~15% chance of past date (overdue)
     * - ~10% chance of today
     * - ~55% chance of future date
     */
    private function generateRandomDueDate(): ?\Carbon\Carbon
    {
        $rand = rand(1, 100);

        if ($rand <= 20) {
            // 20%: No due date
            return null;
        }

        if ($rand <= 35) {
            // 15%: Past date (1-30 days ago)
            return now()->subDays(rand(1, 30));
        }

        if ($rand <= 45) {
            // 10%: Today
            return now();
        }

        // 55%: Future date (1-90 days from now)
        return now()->addDays(rand(1, 90));
    }
}
