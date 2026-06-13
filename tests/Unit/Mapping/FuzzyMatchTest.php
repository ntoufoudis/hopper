<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Strategies\FuzzyMatch;

it('matches a near-identical header above the threshold', function () {
    $suggestion = (new FuzzyMatch(0.8))->suggest('Namee', ['name', 'email']);

    expect($suggestion?->field)->toBe('name')
        ->and($suggestion?->strategy)->toBe('fuzzy')
        ->and($suggestion?->confidence)->toBeGreaterThanOrEqual(0.8);
});

it('returns null for a near-miss below the threshold', function () {
    expect((new FuzzyMatch(0.8))->suggest('phone', ['name', 'email']))->toBeNull();
});
