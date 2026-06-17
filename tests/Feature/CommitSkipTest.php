<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\SkippingCustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('counts skipped verdicts and does not write them on commit', function () {
    // customers.csv has five rows, one of which is "Bob" (resolved to Skip).
    $run = Hopper::define(SkippingCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    $run->commit();
    $run->refresh();

    expect($run->skipped)->toBe(1)
        ->and($run->inserted)->toBe(4)
        ->and(Customer::query()->count())->toBe(4)
        ->and(Customer::query()->where('name', 'Bob')->exists())->toBeFalse();
});
