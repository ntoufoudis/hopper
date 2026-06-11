<?php

declare(strict_types=1);

it('boots the provider and merges the package config', function () {
    expect(config('hopper.default_chunk_size'))->toBe(500)
        ->and(config('hopper.audit.driver'))->toBe('database')
        ->and(config('hopper.tables.runs'))->toBe('hopper_runs');
});

it('publishes the config file under the hopper-config tag', function () {
    $this->artisan('vendor:publish', ['--tag' => 'hopper-config'])
        ->assertSuccessful();

    expect(file_exists(config_path('hopper.php')))->toBeTrue();
});
