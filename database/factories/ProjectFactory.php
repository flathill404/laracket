<?php

declare(strict_types=1);

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
            'slug' => $this->faker->slug(),
            'name' => $this->faker->catchPhrase().' Project',
            'description' => $this->faker->realText(50),
            'organization_id' => \App\Models\Organization::factory(),
        ];
    }
}
