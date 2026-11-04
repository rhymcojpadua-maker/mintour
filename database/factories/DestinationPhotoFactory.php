<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinationPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'path'           => 'destinations/gallery/placeholder.jpg',
            'caption'        => fake()->sentence(4),
            'sort_order'     => fake()->numberBetween(0, 10),
        ];
    }
}
