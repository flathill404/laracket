<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->realText(200),
            'display_order' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(TicketStatus::cases()),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'project_id' => \App\Models\Project::factory(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
