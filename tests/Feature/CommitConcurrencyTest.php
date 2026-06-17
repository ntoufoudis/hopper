<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\CustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('does not double-write when commit is dispatched twice', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    $expectedInserts = Customer::query()->count() + $run->preview()->inserts;

    $run->commit(); // first commit completes (sync)
    $run->refresh()->commit(); // duplicate re-entry must be refused

    expect(Customer::query()->count())->toBe($expectedInserts);

    $run->refresh();
    expect($run->inserted)->toBe($run->preview()->inserts);
});
