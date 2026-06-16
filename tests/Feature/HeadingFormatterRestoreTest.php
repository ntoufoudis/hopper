<?php

declare(strict_types=1);

use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Ntoufoudis\Hopper\Sources\CsvSource;

afterEach(fn () => HeadingRowFormatter::reset());

it('restores the global heading-row formatter after reading headers', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers.csv');

    $source->headers();

    // If 'none' leaked, format() would return the label verbatim.
    expect(HeadingRowFormatter::format(['Full Name'])[0])->not->toBe('Full Name');
});

it('restores the global heading-row formatter after streaming rows', function () {
    $source = CsvSource::make(__DIR__.'/../Fixtures/customers.csv');

    iterator_to_array($source->rows());

    expect(HeadingRowFormatter::format(['Full Name'])[0])->not->toBe('Full Name');
});
