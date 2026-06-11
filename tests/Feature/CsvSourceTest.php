<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Sources\CsvSource;

beforeEach(function () {
    $this->csv = __DIR__.'/../Fixtures/customers.csv';
});

it('reads headers in order', function () {
    $source = CsvSource::make($this->csv);

    expect($source->headers())->toBe(['name', 'email']);
});

it('streams rows keyed by header, numbered from 1', function () {
    $source = CsvSource::make($this->csv);

    $rows = [];
    foreach ($source->rows() as $number => $row) {
        $rows[$number] = $row;
    }

    expect($rows)->toHaveCount(5)
        ->and($rows[1])->toBe(['name' => 'Alice', 'email' => 'alice@example.com'])
        ->and($rows[5])->toBe(['name' => 'Eve', 'email' => 'eve@example.com']);
});

it('produces a stable fingerprint for identical content', function () {
    expect(CsvSource::make($this->csv)->fingerprint())
        ->toBe(CsvSource::make($this->csv)->fingerprint());
});

it('returns a generator, not a materialised array', function () {
    expect(CsvSource::make($this->csv)->rows())->toBeInstanceOf(Generator::class);
});
