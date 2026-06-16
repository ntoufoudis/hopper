<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Staging\Committer;
use Ntoufoudis\Hopper\Tests\Fixtures\Customer;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

function stageCustomers(): ImportRun
{
    return Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();
}

it('keeps staging rows isolated per run when the same file is staged twice', function () {
    $a = stageCustomers();
    $b = stageCustomers();

    expect(StagingRow::count())->toBe(10)
        ->and(StagingRow::where('run_id', $a->id)->count())->toBe(5)
        ->and(StagingRow::where('run_id', $b->id)->count())->toBe(5);
});

it('resumes commit without double-inserting committed rows', function () {
    $run = stageCustomers();

    // Commit only the first chunk (chunkSize = 2), simulating an interruption.
    $committed = app(Committer::class)->commitChunk($run);
    expect($committed)->toBe(2)
        ->and(Customer::count())->toBe(2);

    // Resume: commit the remaining rows.
    $run->commit();

    expect(Customer::count())->toBe(5)
        ->and(StagingRow::whereNull('committed_at')->count())->toBe(0)
        ->and($run->refresh()->inserted)->toBe(5);
});

it('reports progress as processed/total/percentage', function () {
    $run = stageCustomers();

    expect($run->progress())->toBe(['processed' => 0, 'total' => 5, 'percentage' => 0]);

    app(Committer::class)->commitChunk($run);

    expect($run->refresh()->progress())->toBe(['processed' => 2, 'total' => 5, 'percentage' => 40]);

    $run->commit();

    expect($run->refresh()->progress())->toBe(['processed' => 5, 'total' => 5, 'percentage' => 100]);
});
