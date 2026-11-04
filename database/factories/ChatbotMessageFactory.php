<?php

namespace Database\Factories;

use App\Enums\ChatRole;
use App\Models\ChatbotSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatbotMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chatbot_session_id' => ChatbotSession::factory(),
            'role'               => fake()->randomElement([ChatRole::User, ChatRole::Assistant]),
            'content'            => fake()->sentence(12),
            'lang'               => fake()->randomElement(['en', 'fil']),
            'retrieved_kb_ids'   => null,
            'tokens_used'        => fake()->numberBetween(20, 300),
            'latency_ms'         => fake()->numberBetween(400, 2000),
            'feedback'           => null,
        ];
    }
}
