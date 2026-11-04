<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->words(2, true));

        return [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'maker'       => fake()->name(),
            'price'       => fake()->numberBetween(80, 900),
            'description' => fake()->sentence(10),
            'is_published'=> true,
        ];
    }
}
