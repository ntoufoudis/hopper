<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\Committer;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\ExplodingImport;

it('stamps started_at and completed_at on a successful commit', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();

    app(Committer::class)->commit($run);

    $run->refresh();
    expect($run->status)->toBe(RunStatus::Completed)
        ->and($run->started_at)->not->toBeNull()
        ->and($run->completed_at)->not->toBeNull();
});

it('marks a run partially completed when a later chunk fails after earlier chunks committed', function () {
    $run = Hopper::define(ExplodingImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers_boom.csv'))
        ->stage();

    expect(fn () => app(Committer::class)->commit($run))->toThrow(RuntimeException::class);

    $run->refresh();
    expect($run->status)->toBe(RunStatus::PartiallyCompleted)
        ->and($run->inserted)->toBe(2)
        ->and($run->started_at)->not->toBeNull()
        ->and($run->completed_at)->toBeNull();
});
