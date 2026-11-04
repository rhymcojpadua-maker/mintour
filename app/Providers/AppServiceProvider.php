<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\ChatService;
use App\Services\GroqService;
use App\Services\KnowledgeRetriever;
use App\Services\TourismDataRetriever;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GroqService::class);
        $this->app->singleton(KnowledgeRetriever::class);
        $this->app->singleton(TourismDataRetriever::class);
        $this->app->bind(ChatService::class, fn ($app) => new ChatService(
            $app->make(KnowledgeRetriever::class),
            $app->make(TourismDataRetriever::class),
            $app->make(GroqService::class),
        ));
    }

    public function boot(): void
    {
        RateLimiter::for('chatbot', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute((int) Setting::get('rate_limit', config('groq.rate_limit', 20)))->by($key);
        });
    }
}
