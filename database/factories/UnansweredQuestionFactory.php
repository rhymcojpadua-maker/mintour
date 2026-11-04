<?php

namespace Database\Factories;

use App\Models\ChatbotSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnansweredQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chatbot_session_id' => ChatbotSession::factory(),
            'user_id'            => null,
            'question'           => rtrim(fake()->sentence(), '.') . '?',
            'lang'               => fake()->randomElement(['en', 'fil']),
            'is_resolved'        => false,
        ];
    }
}
