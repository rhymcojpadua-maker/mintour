<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question'     => rtrim(fake()->sentence(), '.') . '?',
            'answer'       => fake()->paragraph(),
            'category'     => fake()->randomElement(['Transport','Food','Accommodation','Safety','General']),
            'sort_order'   => fake()->numberBetween(0, 20),
            'is_published' => true,
        ];
    }
}
