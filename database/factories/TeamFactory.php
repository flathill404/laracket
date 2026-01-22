<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle().' Team',
            'display_name' => $this->faker->jobTitle().' Team',
            'organization_id' => \App\Models\Organization::factory(),
        ];
    }
}
