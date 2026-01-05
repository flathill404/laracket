<?php

namespace Database\Factories;

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
            'deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement(['Open', 'In Progress', 'In Review', 'Done']),
            'project_id' => \App\Models\Project::factory(),
        ];
    }
}
