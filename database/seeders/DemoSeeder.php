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
            $this->command?->error("File not found: $yamlPath");

            return;
        }

        $this->command?->info('');
        $this->command?->info('🚀 Starting Demo Seeder...');
        $this->command?->info('');

        $data = \Symfony\Component\Yaml\Yaml::parseFile($yamlPath);

        // 1. Users (Jeison First)
        $this->command?->info('👤 Creating users...');
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
        $this->command?->line("   ✓ Created user: {$jeison->display_name}");

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
            $this->command?->line("   ✓ Created user: {$user->display_name}");
        }
        $this->command?->info('   → '.count($users).' users created');
        $this->command?->info('');

        // 2. Personal Projects (Actions)
        $this->command?->info('🏠 Creating personal organizations...');
        $createOrganization = app(CreateOrganization::class);
        foreach ($users as $user) {
            $org = $createOrganization($user, [
                'name' => 'the-'.$user->name.'-project',
                'display_name' => 'The '.$user->display_name.' Project',
            ]);
            $this->command?->line("   ✓ Created: {$org->display_name}");
        }
        $this->command?->info('   → '.count($users).' personal organizations created');
        $this->command?->info('');

        // 3. Organizations (YAML)
        $this->command?->info('🏢 Creating organizations from YAML...');
        $organizations = [];
        foreach ($data['organizations'] ?? [] as $orgData) {
            $org = Organization::factory()->create([
                'name' => $orgData['name'],
                'display_name' => $orgData['display_name'],
            ]);
            $organizations[$orgData['name']] = $org;
            $this->command?->line("   ✓ Created: {$org->display_name}");
        }
        $this->command?->info('   → '.count($organizations).' organizations created');
        $this->command?->info('');

        // 4. Teams
        $this->command?->info('👥 Creating teams...');
        $teams = [];
        foreach ($data['teams'] ?? [] as $teamData) {
            if (! isset($organizations[$teamData['organization_name']])) {
                continue;
            }
            $team = Team::factory()->create([
                'organization_id' => $organizations[$teamData['organization_name']]->id,
                'name' => $teamData['name'],
                'display_name' => $teamData['display_name'],
            ]);
            $teams[$teamData['name']] = $team;
            $this->command?->line("   ✓ Created: {$team->display_name}");
        }
        $this->command?->info('   → '.count($teams).' teams created');
        $this->command?->info('');

        // 5. Projects (YAML)
        $this->command?->info('📁 Creating projects...');
        $projects = [];
        foreach ($data['projects'] ?? [] as $projectData) {
            if (! isset($organizations[$projectData['organization_name']])) {
                continue;
            }
            $project = Project::factory()->create([
                'organization_id' => $organizations[$projectData['organization_name']]->id,
                'name' => $projectData['name'],
                'display_name' => $projectData['display_name'],
            ]);
            $projects[$projectData['name']] = $project;
            $this->command?->line("   ✓ Created: {$project->display_name}");
        }
        $this->command?->info('   → '.count($projects).' projects created');
        $this->command?->info('');

        // Link Projects to Teams (All teams in Org get access to all projects in Org for demo)
        $this->command?->info('🔗 Linking projects to teams...');
        foreach ($projects as $project) {
            $orgTeams = $project->organization->teams;
            $project->assignedTeams()->sync($orgTeams);
            $this->command?->line("   ✓ Linked: {$project->display_name} → ".$orgTeams->count().' teams');
        }
        $this->command?->info('');

        // 6. Attach Users to Orgs and Teams
        $this->command?->info('🔐 Attaching users to organizations and teams...');
        $attachmentCount = 0;
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
                    $attachmentCount++;
                }
            }

            foreach ($userData['teams'] ?? [] as $teamName) {
                if (isset($teams[$teamName])) {
                    $teams[$teamName]->users()->attach($user->id, ['role' => TeamRole::Member]);
                    $attachmentCount++;
                }
            }
        }
        $this->command?->info("   → {$attachmentCount} memberships created");
        $this->command?->info('');

        // 7. Tickets
        $this->command?->info('🎫 Creating tickets...');
        $ticketCount = 0;
        $commentCount = 0;
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
            $ticketCount++;

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
                    $commentCount++;
                }
            }
        }
        $this->command?->info("   → {$ticketCount} tickets created");
        $this->command?->info("   → {$commentCount} comments created");
        $this->command?->info('');

        // 8. Build search index
        $this->command?->info('🔍 Building search index...');
        Artisan::call('scout:import', ['model' => Ticket::class]);
        $this->command?->info('   → Search index built');
        $this->command?->info('');
        $this->command?->info('✅ Demo seeding completed!');
        $this->command?->info('');
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
