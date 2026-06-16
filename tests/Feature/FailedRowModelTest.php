<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;

it('persists a failed row with a json payload and a reason', function () {
    $run = ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);

    $failed = FailedRow::create([
        'run_id' => $run->id,
        'source_row_number' => 3,
        'row_hash' => hash('sha256', 'fp:3'),
        'payload' => ['name' => 'Carol', 'email' => 'not-an-email'],
        'reason' => 'The email must be a valid email address.',
    ]);

    $fresh = $failed->fresh();

    expect($fresh->payload)->toBe(['name' => 'Carol', 'email' => 'not-an-email'])
        ->and($fresh->reason)->toBe('The email must be a valid email address.')
        ->and($fresh->source_row_number)->toBe(3)
        ->and($fresh->getTable())->toBe('hopper_failed_rows');
});

it('enforces a unique row_hash', function () {
    $run = ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);

    $attributes = [
        'run_id' => $run->id,
        'source_row_number' => 3,
        'row_hash' => hash('sha256', 'fp:3'),
        'payload' => ['name' => 'Carol'],
        'reason' => 'bad',
    ];

    FailedRow::create($attributes);

    expect(fn () => FailedRow::create($attributes))->toThrow(QueryException::class);
});
