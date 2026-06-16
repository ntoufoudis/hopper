<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\StagingWriter;
use Ntoufoudis\Hopper\Tests\Fixtures\PipelineCustomerImport;

function makePipelineRun(CsvSource $source): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Staging,
        'import_definition' => PipelineCustomerImport::class,
        'source_fingerprint' => $source->fingerprint(),
    ]);
}

it('stages only valid rows and diverts rejected + invalid rows', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers_messy.csv');
    $run = makePipelineRun($source);

    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);

    // Rows 1 (Alice) and 4 (Dave) stage; rows 2 (blank name) and 3 (bad email) divert.
    expect(StagingRow::where('run_id', $run->id)->count())->toBe(2)
        ->and(FailedRow::where('run_id', $run->id)->count())->toBe(2);

    $run->refresh();
    expect($run->total)->toBe(2)
        ->and($run->failed)->toBe(2);
});

it('applies transforms before validation and stages the transformed payload', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers_messy.csv');
    $run = makePipelineRun($source);

    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);

    $alice = StagingRow::where('run_id', $run->id)->where('source_row_number', 1)->first();

    expect($alice->resolution)->toBe(ResolutionType::Insert)
        ->and($alice->payload)->toBe(['name' => 'Alice', 'email' => 'alice@example.com']);
});

it('records the rejection reason from the pipe and the validator', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers_messy.csv');
    $run = makePipelineRun($source);

    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);

    $blankName = FailedRow::where('run_id', $run->id)->where('source_row_number', 2)->first();
    $badEmail = FailedRow::where('run_id', $run->id)->where('source_row_number', 3)->first();

    expect($blankName->reason)->toBe('name is required')
        ->and($badEmail->reason)->toContain('email');
});

it('is idempotent on re-write of the same run for both staged and failed rows', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers_messy.csv');
    $run = makePipelineRun($source);

    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);
    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);

    expect(StagingRow::count())->toBe(2)
        ->and(FailedRow::count())->toBe(2);
});
