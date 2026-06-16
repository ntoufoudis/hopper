<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\UpsertCustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('preview counts exactly equal the subsequent commit results', function () {
    // Two existing rows => 2 updates; the other three => 3 inserts.
    Customer::create(['name' => 'Old Alice', 'email' => 'alice@example.com']);
    Customer::create(['name' => 'Old Bob', 'email' => 'bob@example.com']);

    $run = Hopper::define(UpsertCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    $preview = $run->preview();

    expect($preview->inserts)->toBe(3)
        ->and($preview->updates)->toBe(2)
        ->and($preview->skips)->toBe(0)
        ->and($preview->errors)->toBe(0)
        ->and($preview->total)->toBe(5);

    $run->commit();
    $run->refresh();

    // Parity: every preview number equals the committed result.
    expect($run->inserted)->toBe($preview->inserts)
        ->and($run->updated)->toBe($preview->updates)
        ->and($run->skipped)->toBe($preview->skips)
        ->and($run->processed)->toBe($preview->valid)
        ->and(Customer::count())->toBe($preview->inserts + 2);
});
