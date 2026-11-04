<?php

namespace Tests\Unit;

use App\Services\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_grounded_chat_completion_and_returns_reply(): void
    {
        config()->set('groq.key', 'test-key');

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['role' => 'assistant', 'content' => 'Silonay Eco-Park is a **mangrove forest** 12 km away.'],
                ]],
                'usage' => ['total_tokens' => 55],
            ]),
        ]);

        $service = new GroqService('test-key');
        $result = $service->ask(
            'Tell me about Silonay',
            [['id' => 1, 'title' => 'Silonay', 'category' => 'Nature', 'content' => 'Silonay is 12 km from town.', 'source' => null]],
            'en',
            [['role' => 'user', 'text' => 'Recommend a nature trip.'], ['role' => 'model', 'text' => 'What is your budget?']],
        );

        $this->assertSame('Silonay Eco-Park is a **mangrove forest** 12 km away.', $result['reply']);
        $this->assertSame(55, $result['tokens']);
        $this->assertTrue($result['grounded']);
        $this->assertGreaterThan(0, $result['latency_ms']);

        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'];
            $systemPrompt = $messages[0]['content'];

            return str_contains($systemPrompt, 'STRICT RULES')
                && str_contains($systemPrompt, 'do not claim a place fits their budget')
                && str_contains($systemPrompt, 'free/no entrance fee does not mean the whole visit is free')
                && str_contains($systemPrompt, 'MinTour directory/map records are useful leads')
                && $messages[1] === ['role' => 'user', 'content' => 'Recommend a nature trip.']
                && $messages[2] === ['role' => 'assistant', 'content' => 'What is your budget?']
                && str_contains($messages[3]['content'], 'Silonay is 12 km from town')
                && str_contains($request->url(), '/openai/v1/chat/completions')
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && ! str_contains($request->url(), 'test-key');
        });
    }

    public function test_it_throws_when_api_key_is_not_configured(): void
    {
        config()->set('groq.key', null);
        $service = new GroqService;

        $this->expectException(\RuntimeException::class);
        $service->ask('hi', [], 'en');
    }

    public function test_it_throws_a_friendly_message_on_429(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => ['message' => 'quota']], 429),
        ]);

        $service = new GroqService('test-key');

        try {
            $service->ask('hi', [], 'en');
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('too many requests', $e->getMessage());
        }
    }

    public function test_it_explains_an_unavailable_model_without_leaking_provider_details(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => ['message' => 'The requested model is not available.'],
            ], 404),
        ]);

        $service = new GroqService('test-key');

        try {
            $service->ask('Tell me about Victoria', [], 'en');
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertSame(404, $e->getCode());
            $this->assertSame(
                'The configured AI model is unavailable. Please ask the site administrator to check the Groq model setting.',
                $e->getMessage(),
            );
        }
    }
}
