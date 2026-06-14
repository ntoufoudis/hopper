<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\MappingTemplate;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Customer;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

function autoMapAliasedRun(): ImportRun
{
    return Hopper::define(CustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers_aliased.csv'))
        ->autoMap()
        ->stage();
}

it('auto-maps mismatched headers onto target fields and commits correctly', function () {
    $run = autoMapAliasedRun();

    // A template was learned for this header layout.
    expect(MappingTemplate::count())->toBe(1);

    $row = StagingRow::where('run_id', $run->id)->orderBy('source_row_number')->first();
    expect(array_keys($row->payload))->toBe(['name', 'email'])
        ->and($row->payload['name'])->toBe('Alice');

    $run->commit();

    expect(Customer::count())->toBe(5)
        ->and(Customer::where('email', 'alice@example.com')->where('name', 'Alice')->exists())->toBeTrue();
});

it('reuses the persisted template on a second import with zero re-mapping', function () {
    // First import learns and saves the template.
    autoMapAliasedRun();
    expect(MappingTemplate::count())->toBe(1);

    // Tamper with the saved map so a swap is observable: if strategies ran
    // again they would re-derive name/email; the template must win instead.
    $template = MappingTemplate::first();
    $template->update(['column_map' => ['Full Name' => 'email', 'E-Mail Address' => 'name']]);

    $run = autoMapAliasedRun();

    expect(MappingTemplate::count())->toBe(1);

    $row = StagingRow::where('run_id', $run->id)->orderBy('source_row_number')->first();
    expect($row->payload)->toBe(['email' => 'Alice', 'name' => 'alice@example.com']);
});
