<?php

namespace Database\Factories;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'        => fake()->sentence(6),
            'body'         => fake()->paragraph(),
            'priority'     => fake()->randomElement([Priority::Low, Priority::Normal, Priority::High]),
            'is_active'    => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 15)),
        ];
    }
}
