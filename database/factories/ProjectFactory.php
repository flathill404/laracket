<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->catchPhrase().' Project',
            'display_name' => $this->faker->catchPhrase().' Project',
            'summary' => $this->faker->realText(50),
            'deadline' => $this->faker->dateTimeBetween('now', '+1 year'),
            'organization_id' => \App\Models\Organization::factory(),
        ];
    }
}
