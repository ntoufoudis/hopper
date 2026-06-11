<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;

it('casts status and computes progress', function () {
    $run = ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
        'total' => 4,
        'processed' => 1,
    ]);

    expect($run->status)->toBe(RunStatus::Ready)
        ->and($run->progress())->toBe(['processed' => 1, 'total' => 4, 'percentage' => 25]);
});

it('returns zero percentage when total is null', function () {
    $run = ImportRun::create([
        'status' => RunStatus::Pending,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);

    expect($run->progress())->toBe(['processed' => 0, 'total' => 0, 'percentage' => 0]);
});

it('casts staging payload to array and resolution to enum', function () {
    $row = StagingRow::create([
        'run_id' => 1,
        'source_row_number' => 1,
        'row_hash' => 'h1',
        'payload' => ['email' => 'a@b.test'],
        'resolution' => ResolutionType::Insert->value,
    ]);

    expect($row->fresh()->payload)->toBe(['email' => 'a@b.test'])
        ->and($row->fresh()->resolution)->toBe(ResolutionType::Insert);
});
