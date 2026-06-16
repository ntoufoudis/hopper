<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\BrokenImport;

it('propagates a commit failure as a catchable exception on the sync queue', function () {
    $run = Hopper::define(BrokenImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers.csv'))
        ->stage();

    // A failing commit must surface as a normal, catchable exception - not a
    // fatal "exception thrown in destructor" from PendingDispatch::__destruct().
    // (Use a concrete class: Pest's toThrow() treats the interface name
    // "Throwable" as an expected message substring, not an instanceof check.)
    expect(fn () => $run->commit())->toThrow(QueryException::class);
});
