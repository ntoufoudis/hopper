<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

it('defaults fields() to the model fillable', function () {
    expect((new CustomerImport)->fields())->toBe(['name', 'email']);
});

it('stages rows under target field keys when an explicit map is applied', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers_aliased.csv'))
        ->map(new ColumnMap(['Full Name' => 'name', 'E-Mail Address' => 'email']))
        ->stage();

    $row = StagingRow::where('run_id', $run->id)->orderBy('source_row_number')->first();

    expect(array_keys($row->payload))->toBe(['name', 'email'])
        ->and($row->payload['name'])->toBe('Alice')
        ->and($row->payload['email'])->toBe('alice@example.com');
});

it('stages raw header keys when no map is applied (M1 behaviour preserved)', function () {
    $run = Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();

    $row = StagingRow::where('run_id', $run->id)->orderBy('source_row_number')->first();

    expect(array_keys($row->payload))->toBe(['name', 'email']);
});
