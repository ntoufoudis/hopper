<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Sources\ExcelSource;

it('returns a stable fingerprint across repeated calls (csv)', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers.csv');

    expect($source->fingerprint())->toBe($source->fingerprint());
});

it('returns a stable fingerprint across repeated calls (excel)', function () {
    $source = ExcelSource::make(__DIR__.'/../Fixtures/customers.csv');

    expect($source->fingerprint())->toBe($source->fingerprint());
});
