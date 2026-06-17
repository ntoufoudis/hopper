<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\StagingWriter;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\PipelineCustomerImport;

it('preview() total and progress() total agree (staged-only)', function () {
    // customers_messy.csv: 2 rows stage, 2 are diverted (blank name + bad email).
    $source = CsvSource::make(__DIR__.'/../Fixtures/csv/customers_messy.csv');

    $run = ImportRun::create([
        'status' => RunStatus::Pending,
        'import_definition' => PipelineCustomerImport::class,
        'source_fingerprint' => $source->fingerprint(),
    ]);

    app(StagingWriter::class)->write($run, $source, new PipelineCustomerImport);
    $run->refresh();

    $preview = $run->preview();

    expect($preview->total)->toBe($run->progress()['total'])
        ->and($preview->total)->toBe(2);   // staged-only: failed rows are not in total
});
