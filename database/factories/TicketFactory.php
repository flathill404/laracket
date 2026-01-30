<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Track issue numbers per project during factory creation.
     *
     * @var array<string, int>
     */
    private static array $issueNumbers = [];

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->realText(200),
            'display_order' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(TicketStatus::cases()),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'project_id' => \App\Models\Project::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Ticket $ticket) {
            if ($ticket->issue_number === null) {
                $projectId = (string) $ticket->project_id;

                // Get the current max from database if not cached
                if (! isset(self::$issueNumbers[$projectId])) {
                    self::$issueNumbers[$projectId] = Ticket::where('project_id', $projectId)->max('issue_number') ?? 0;
                }

                // Increment and assign
                self::$issueNumbers[$projectId]++;
                $ticket->issue_number = self::$issueNumbers[$projectId];
            }
        });
    }

    /**
     * Reset the issue number counters (useful for testing).
     */
    public static function resetIssueNumbers(): void
    {
        self::$issueNumbers = [];
    }
}
