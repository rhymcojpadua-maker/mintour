<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name  = fake()->unique()->randomElement(['Beaches','Nature','Culture','Food','Adventure','Churches','Markets']);
        $icons = ['🌊','🌿','⛪','🍽️','⛰️','🕍','🛍️'];
        $colors = ['#06b6d4','#0f766e','#f59e0b','#fb7185','#8b5cf6','#f43f5e','#10b981'];

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'icon'        => fake()->randomElement($icons),
            'color'       => fake()->randomElement($colors),
            'description' => fake()->sentence(),
            'sort_order'  => fake()->numberBetween(0, 20),
            'is_active'   => true,
        ];
    }
}
