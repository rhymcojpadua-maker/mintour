<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChatbotSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => null,
            'guest_token'     => Str::random(48),
            'lang'            => fake()->randomElement(['en', 'fil']),
            'ip_address'      => fake()->ipv4(),
            'user_agent'      => fake()->userAgent(),
            'message_count'   => 0,
            'last_active_at'  => now(),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn () => [
            'user_id'     => $user?->id ?? User::factory(),
            'guest_token' => null,
        ]);
    }
}
