<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Support\ServiceProvider;

final class HopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hopper.php', 'hopper');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/hopper.php' => $this->app->configPath('hopper.php'),
            ], 'hopper-config');
        }
    }
}
