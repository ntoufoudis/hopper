<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Staging\ImportPreview;

it('exposes its counts and serialises to an array', function () {
    $preview = new ImportPreview(total: 10, valid: 8, errors: 2, inserts: 5, updates: 2, skips: 1);

    expect($preview->total)->toBe(10)
        ->and($preview->valid)->toBe(8)
        ->and($preview->errors)->toBe(2)
        ->and($preview->inserts)->toBe(5)
        ->and($preview->updates)->toBe(2)
        ->and($preview->skips)->toBe(1)
        ->and($preview->toArray())->toBe([
            'total' => 10,
            'valid' => 8,
            'errors' => 2,
            'inserts' => 5,
            'updates' => 2,
            'skips' => 1,
        ]);
});
