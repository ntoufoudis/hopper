<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\PipelineCustomerImport;

it('cascades a run delete to its staging and failed rows', function () {
    $run = Hopper::define(PipelineCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers_messy.csv'))
        ->stage();

    expect(StagingRow::where('run_id', $run->id)->count())->toBeGreaterThan(0)
        ->and(FailedRow::where('run_id', $run->id)->count())->toBeGreaterThan(0);

    $run->delete();

    expect(StagingRow::where('run_id', $run->id)->count())->toBe(0)
        ->and(FailedRow::where('run_id', $run->id)->count())->toBe(0);
});
