<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\CustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('commits staged rows into the target with correct counts', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    $run->commit();

    $run->refresh();

    expect(Customer::count())->toBe(5)
        ->and($run->status)->toBe(RunStatus::Completed)
        ->and($run->inserted)->toBe(5)
        ->and($run->processed)->toBe(5)
        ->and($run->updated)->toBe(0)
        ->and($run->skipped)->toBe(0)
        ->and(StagingRow::whereNull('committed_at')->count())->toBe(0)
        ->and(Customer::where('email', 'alice@example.com')->exists())->toBeTrue();
});
