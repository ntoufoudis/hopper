<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\StagingWriter;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

function makeRun(CsvSource $source): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Staging,
        'import_definition' => CustomerImport::class,
        'source_fingerprint' => $source->fingerprint(),
    ]);
}

it('stages every source row with an insert verdict and content hash', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers.csv');
    $run = makeRun($source);

    app(StagingWriter::class)->write($run, $source, new CustomerImport);

    expect(StagingRow::count())->toBe(5);

    $first = StagingRow::orderBy('source_row_number')->first();
    expect($first->source_row_number)->toBe(1)
        ->and($first->resolution)->toBe(ResolutionType::Insert)
        ->and($first->payload)->toBe(['name' => 'Alice', 'email' => 'alice@example.com'])
        ->and($first->committed_at)->toBeNull()
        ->and($first->row_hash)->toBe(hash('sha256', $source->fingerprint().':1'));
});

it('is idempotent on re-write (no duplicate staging rows)', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers.csv');

    app(StagingWriter::class)->write(makeRun($source), $source, new CustomerImport);
    app(StagingWriter::class)->write(makeRun($source), $source, new CustomerImport);

    expect(StagingRow::count())->toBe(5);
});
