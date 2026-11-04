<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DestinationFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true));

        return [
            'category_id'       => Category::factory(),
            'name'              => $name,
            'slug'              => Str::slug($name),
            'tagline'           => fake()->randomElement(['Nature Reserve', 'Coastal', 'Heritage Site', 'Local Market', 'Viewpoint']),
            'short_description' => fake()->sentence(15),
            'long_description'  => fake()->paragraphs(3, true),
            'latitude'          => fake()->latitude(13.10, 13.25),
            'longitude'         => fake()->longitude(121.25, 121.35),
            'address'           => 'Brgy. ' . fake()->lastName() . ', Victoria',
            'hours'             => '6:00 AM – 6:00 PM daily',
            'entrance_fee'      => '₱' . fake()->numberBetween(20, 200),
            'best_time'         => fake()->randomElement(['Early morning', 'Sunset', 'Weekends']),
            'distance_km'       => fake()->randomFloat(1, 0, 30),
            'rating_avg'        => 0,
            'rating_count'      => 0,
            'is_featured'       => fake()->boolean(30),
            'is_published'      => true,
            'sort_order'        => fake()->numberBetween(0, 50),
        ];
    }
}
