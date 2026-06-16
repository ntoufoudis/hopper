<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\ExcelServiceProvider;
use Ntoufoudis\Hopper\HopperServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExcelServiceProvider::class,
            HopperServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('queue.default', 'sync');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Testbench only runs the migration paths loaded here; provider-registered
        // paths are not auto-run in tests. Load Hopper's own
        // migrations plus the target-table fixtures the suite needs.
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
    }
}
