<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmergencyContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->company() . ' Victoria',
            'phone'      => '(043) 288-' . fake()->numerify('####'),
            'category'   => fake()->randomElement(['Police','Fire','Medical','Coast Guard','Tourism']),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
