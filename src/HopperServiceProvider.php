<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Mapping\Strategies\AliasMatch;
use Ntoufoudis\Hopper\Mapping\Strategies\ExactMatch;
use Ntoufoudis\Hopper\Mapping\Strategies\FuzzyMatch;

final class HopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hopper.php', 'hopper');

        $this->app->singleton(HopperManager::class);

        $this->app->singleton(Mapper::class, function (): Mapper {
            /** @var array<string, list<string>> $aliases */
            $aliases = [];

            foreach (Config::array('hopper.mapping.aliases') as $field => $list) {
                $values = [];

                foreach ((array) $list as $alias) {
                    if (is_scalar($alias)) {
                        $values[] = (string) $alias;
                    }
                }

                $aliases[(string) $field] = $values;
            }

            return new Mapper([
                new ExactMatch,
                new AliasMatch($aliases),
                new FuzzyMatch(Config::float('hopper.mapping.fuzzy_threshold')),
            ]);
        });
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
