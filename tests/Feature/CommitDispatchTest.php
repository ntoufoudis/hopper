<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\BrokenImport;

it('propagates a commit failure as a catchable exception on the sync queue', function () {
    $run = Hopper::define(BrokenImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();

    // A failing commit must surface as a normal, catchable Throwable - not a
    // fatal "exception thrown in destructor" from PendingDispatch::__destruct().
    expect(fn () => $run->commit())->toThrow(Throwable::class);
});
