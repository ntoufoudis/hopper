<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Export\FailedRowExporter;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;

function makeFailedRun(): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);
}

it('emits a header row, the original payload columns, and the reason', function () {
    $run = makeFailedRun();

    FailedRow::create([
        'run_id' => $run->id,
        'source_row_number' => 3,
        'row_hash' => hash('sha256', 'fp:3'),
        'payload' => ['name' => 'Carol', 'email' => 'not-an-email'],
        'reason' => 'The email must be a valid email address.',
    ]);

    $csv = app(FailedRowExporter::class)->export($run);
    $lines = array_values(array_filter(explode("\n", trim($csv))));

    expect($lines[0])->toBe('name,email,error')
        ->and($lines[1])->toContain('Carol')
        ->and($lines[1])->toContain('not-an-email')
        ->and($lines[1])->toContain('The email must be a valid email address.');
});

it('neutralises spreadsheet formula injection on leading = + - @', function () {
    $run = makeFailedRun();

    FailedRow::create([
        'run_id' => $run->id,
        'source_row_number' => 1,
        'row_hash' => hash('sha256', 'fp:1'),
        'payload' => ['name' => '=HYPERLINK("http://evil")', 'email' => '+1', 'note' => '@x', 'misc' => '-2'],
        'reason' => 'bad',
    ]);

    $csv = app(FailedRowExporter::class)->export($run);

    expect($csv)->toContain("\t=HYPERLINK")
        ->and($csv)->toContain("\t+1")
        ->and($csv)->toContain("\t@x")
        ->and($csv)->toContain("\t-2");
});

it('only exports the given run\'s failed rows', function () {
    $run = makeFailedRun();
    $other = makeFailedRun();

    FailedRow::create(['run_id' => $run->id, 'source_row_number' => 1, 'row_hash' => 'a', 'payload' => ['name' => 'Mine'], 'reason' => 'r']);
    FailedRow::create(['run_id' => $other->id, 'source_row_number' => 1, 'row_hash' => 'b', 'payload' => ['name' => 'Theirs'], 'reason' => 'r']);

    $csv = app(FailedRowExporter::class)->export($run);

    expect($csv)->toContain('Mine')
        ->and($csv)->not->toContain('Theirs');
});

it('passes the escape argument explicitly so output is PHP 8.4+ stable', function () {
    $run = makeFailedRun();

    FailedRow::create([
        'run_id' => $run->id,
        'source_row_number' => 1,
        'row_hash' => hash('sha256', 'fp:esc:1'),
        'payload' => ['name' => 'a"b', 'path' => 'C:\\data\\'],
        'reason' => 'bad',
    ]);

    $deprecations = [];
    set_error_handler(static function (int $errno, string $message) use (&$deprecations): bool {
        $deprecations[] = $message;

        return true;
    }, E_DEPRECATED);

    try {
        $csv = app(FailedRowExporter::class)->export($run);
    } finally {
        restore_error_handler();
    }

    $lines = array_values(array_filter(explode("\n", trim($csv))));
    // Parse the data line back with the same explicit empty escape.
    $parsed = str_getcsv($lines[1], ',', '"', '');

    expect($deprecations)->toBe([])
        ->and($parsed[0])->toBe('a"b')
        ->and($parsed[1])->toBe('C:\\data\\');
});
