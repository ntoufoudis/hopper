<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\StagingWriter;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\CustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\NoFieldsImport;

function allowListRun(CsvSource $source): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Staging,
        'import_definition' => CustomerImport::class,
        'source_fingerprint' => $source->fingerprint(),
    ]);
}

it('strips columns outside the import target fields when staging', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/csv/customers_extra.csv');
    $run = allowListRun($source);

    app(StagingWriter::class)->write($run, $source, new CustomerImport);

    $first = StagingRow::orderBy('source_row_number')->first();
    expect($first->payload)->toBe(['name' => 'Alice', 'email' => 'alice@example.com']);
});

it('refuses to stage when the import declares no target fields', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv');
    $run = allowListRun($source);

    expect(fn () => app(StagingWriter::class)->write($run, $source, new NoFieldsImport))
        ->toThrow(LogicException::class);
});
