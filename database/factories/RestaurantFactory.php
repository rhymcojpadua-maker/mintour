<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RestaurantFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->words(2, true)) . ' ' . fake()->randomElement(['Kitchen','Grill','Café','Carinderia','Eatery']);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'cuisine'     => fake()->randomElement(['Filipino','Seafood','Café','BBQ','Chinese']),
            'price_range' => '₱' . fake()->numberBetween(80, 300) . '–' . fake()->numberBetween(400, 900),
            'rating_avg'  => fake()->randomFloat(2, 3.5, 5),
            'address'     => fake()->streetAddress() . ', Victoria',
            'latitude'    => fake()->latitude(13.10, 13.25),
            'longitude'   => fake()->longitude(121.25, 121.35),
            'hours'       => fake()->randomElement(['6 AM – 9 PM','7 AM – 10 PM','11 AM – 11 PM']),
            'description' => fake()->paragraph(),
            'is_published'=> true,
        ];
    }
}
