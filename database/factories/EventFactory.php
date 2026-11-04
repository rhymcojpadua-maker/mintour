<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = ucwords(fake()->words(3, true));
        $starts = fake()->dateTimeBetween('+1 week', '+4 months');

        return [
            'title'        => $title,
            'slug'         => Str::slug($title),
            'starts_at'    => $starts,
            'ends_at'      => (clone $starts)->modify('+1 day'),
            'location'     => fake()->randomElement(['Victoria Plaza','Town Proper','Silonay Boardwalk','Victoria Port']),
            'category'     => fake()->randomElement(['Cultural','Eco','Food','Sports','Religious']),
            'description'  => fake()->paragraph(),
            'is_published' => true,
        ];
    }
}
