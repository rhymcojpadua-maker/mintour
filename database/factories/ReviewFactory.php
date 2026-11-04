<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'destination_id' => Destination::factory(),
            'rating'         => fake()->numberBetween(3, 5),
            'body'           => fake()->paragraph(),
            'is_approved'    => true,
        ];
    }
}
