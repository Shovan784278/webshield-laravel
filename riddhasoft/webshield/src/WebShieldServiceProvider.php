<?php

namespace Riddhasoft\Webshield;

use Illuminate\Support\ServiceProvider;

class WebShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/webshield.php', 'webshield'
        );

        $this->app->singleton(WebShieldService::class, function () {
            return new WebShieldService();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/webshield.php' => config_path('webshield.php'),
            ], 'webshield-config');
        }

        $this->registerModelListeners();
    }

    protected function registerModelListeners(): void
    {
        $autoPurge = config('webshield.auto_purge', []);
        $service = $this->app->make(WebShieldService::class);

        foreach (array_keys($autoPurge) as $modelClass) {
            if (class_exists($modelClass)) {
                // Model save/update hole purge
                $modelClass::saved(function ($model) use ($service) {
                    $service->handleAutoPurge($model);
                });

                // Model delete holeo purge
                $modelClass::deleted(function ($model) use ($service) {
                    $service->handleAutoPurge($model);
                });
            }
        }
    }
}