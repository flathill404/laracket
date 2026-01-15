<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:demo {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all tables and seed realistic demo data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirm('This will truncate all data. Do you wish to continue?', $this->option('force'))) {
            return;
        }

        $this->info('Truncating tables...');

        Schema::disableForeignKeyConstraints();

        $tables = [
            'users',
            'organizations',
            'projects',
            'teams',
            'tickets',
            'comments',
            'organization_invitations',
            'organization_user',
            'project_team',
            'project_user',
            'team_user',
            'ticket_user',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Tables truncated.');
        $this->info('Seeding demo data...');

        $this->call('db:seed', [
            '--class' => 'DemoSeeder',
        ]);

        $this->info('Demo data seeded successfully!');
    }
}
