<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\Committer;
use Ntoufoudis\Hopper\Staging\StagingWriter;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\UpsertCustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

function makeUpsertRun(CsvSource $source): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Staging,
        'import_definition' => UpsertCustomerImport::class,
        'source_fingerprint' => $source->fingerprint(),
    ]);
}

it('issues exactly one keyed lookup per chunk during staging', function () {
    // customers.csv has 5 rows; chunkSize 2 => 3 chunks => 3 prime queries.
    $source = CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv');
    $run = makeUpsertRun($source);

    $customerSelects = 0;
    Event::listen(function (QueryExecuted $event) use (&$customerSelects): void {
        if (str_contains($event->sql, 'customers') && str_starts_with(strtolower(trim($event->sql)), 'select')) {
            $customerSelects++;
        }
    });

    app(StagingWriter::class)->write($run, $source, new UpsertCustomerImport);

    expect($customerSelects)->toBe(3)
        ->and(StagingRow::where('run_id', $run->id)->count())->toBe(5);
});

it('produces correct insert and update counts through stage and commit', function () {
    // Pre-seed one matching customer so its row resolves to Update.
    Customer::create(['name' => 'Old Alice', 'email' => 'alice@example.com']);

    $source = CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv');
    $run = makeUpsertRun($source);

    app(StagingWriter::class)->write($run, $source, new UpsertCustomerImport);
    app(Committer::class)->commit($run->refresh());

    $run->refresh();
    expect($run->inserted)->toBe(4)   // Bob, Carol, Dave, Eve
        ->and($run->updated)->toBe(1) // Alice
        ->and($run->skipped)->toBe(0)
        ->and(Customer::where('email', 'alice@example.com')->where('name', 'Alice')->exists())->toBeTrue()
        ->and(Customer::count())->toBe(5);
});
