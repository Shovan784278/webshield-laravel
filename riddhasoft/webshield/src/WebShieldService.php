<?php

namespace Riddhasoft\Webshield;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebShieldService
{
    /**
     * Purge single or dynamic URL
     */
    public function purge(string $path): void
    {
        $proxyUrl = config('webshield.proxy_url', 'http://127.0.0.1:8080');
        $apiKey   = config('webshield.api_key', 'webshield_secret_key_12345');

        try {
            $response = Http::timeout(2)
                ->withHeaders([
                    'X-WebShield-Key' => $apiKey,
                    'Accept'          => 'application/json',
                ])
                ->post(rtrim($proxyUrl, '/') . '/purge', [
                    'path' => $path,
                ]);

            if ($response->successful()) {
                Log::info("WebShield Purge Success: {$path}");
            } else {
                Log::warning("WebShield Purge Failed [{$response->status()}]: {$path}");
            }
        } catch (\Throwable $e) {
            // Proxy offline thakleo Laravel application break korbe na
            Log::error("WebShield Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Handle Eloquent Model Events
     */
    public function handleAutoPurge($model): void
    {
        $modelClass = get_class($model);
        $configList = config('webshield.auto_purge', []);

        if (!isset($configList[$modelClass])) {
            return;
        }

        $routes = (array) $configList[$modelClass];

        foreach ($routes as $routePattern) {
            // Dynamic parameter replace: /blog/{slug} -> /blog/my-slug
            $resolvedPath = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($model) {
                $field = $matches[1];
                return $model->{$field} ?? $matches[0];
            }, $routePattern);

            $this->purge($resolvedPath);
        }
    }
}