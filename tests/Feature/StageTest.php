<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\CustomerImport;

it('stages a CSV into a ready run with the correct total', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    expect($run->status)->toBe(RunStatus::Ready)
        ->and($run->total)->toBe(5)
        ->and($run->import_definition)->toBe(CustomerImport::class)
        ->and(StagingRow::where('run_id', $run->id)->count())->toBe(5);
});
