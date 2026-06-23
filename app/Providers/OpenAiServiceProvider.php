<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI;

class OpenAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenAI::class, function ($app) {
            $apiKey = config('services.openai.api_key');

            if (blank($apiKey)) {
                throw new \RuntimeException('OPENAI_API_KEY is not configured. Add it to your .env file.');
            }

            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->make();
        });

        $this->app->alias(OpenAI::class, 'openai');
    }

    public function boot(): void {}
}
