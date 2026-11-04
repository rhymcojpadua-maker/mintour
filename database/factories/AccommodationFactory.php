<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccommodationFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->words(2, true)) . ' ' . fake()->randomElement(['Resort','Inn','Lodge','Homestay','Pension']);

        return [
            'name'            => $name,
            'slug'            => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'type'            => fake()->randomElement(['Resort','Inn','Eco-Lodge','Homestay','Pension']),
            'price_per_night' => fake()->numberBetween(700, 2500),
            'rating_avg'      => fake()->randomFloat(2, 3.5, 5),
            'address'         => fake()->streetAddress() . ', Victoria',
            'latitude'        => fake()->latitude(13.10, 13.25),
            'longitude'       => fake()->longitude(121.25, 121.35),
            'contact'         => '+63 9' . fake()->numerify('## ### ####'),
            'description'     => fake()->paragraph(),
            'amenities'       => fake()->randomElements(['WiFi','Pool','Aircon','Restaurant','Beachfront','Breakfast','Parking','Pet-friendly'], 3),
            'is_published'    => true,
        ];
    }
}
