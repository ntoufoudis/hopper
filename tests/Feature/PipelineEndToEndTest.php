<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Export\FailedRowExporter;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\PipelineCustomerImport;

it('stages valid rows and diverts the rest end to end via the builder', function () {
    $run = Hopper::define(PipelineCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers_messy.csv'))
        ->stage();

    expect($run->status)->toBe(RunStatus::Ready)
        ->and(StagingRow::where('run_id', $run->id)->count())->toBe(2)
        ->and(FailedRow::where('run_id', $run->id)->count())->toBe(2);

    // Transform ran before validation: Alice's email was lowercased and staged.
    $alice = StagingRow::where('run_id', $run->id)->where('source_row_number', 1)->first();
    expect($alice->payload['email'])->toBe('alice@example.com');

    // The diverted rows export with their reasons.
    $csv = app(FailedRowExporter::class)->export($run);
    expect($csv)->toContain('name is required')
        ->and($csv)->toContain('email');
});
