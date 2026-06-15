<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Ntoufoudis\Hopper\Models\FailedRow;

it('persists a failed row with a json payload and a reason', function () {
    $failed = FailedRow::create([
        'run_id' => 1,
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
    $attributes = [
        'run_id' => 1,
        'source_row_number' => 3,
        'row_hash' => hash('sha256', 'fp:3'),
        'payload' => ['name' => 'Carol'],
        'reason' => 'bad',
    ];

    FailedRow::create($attributes);

    expect(fn () => FailedRow::create($attributes))->toThrow(QueryException::class);
});
