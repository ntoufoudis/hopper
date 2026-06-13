<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Strategies\ExactMatch;

it('matches a header to a field case-insensitively with full confidence', function () {
    $suggestion = (new ExactMatch)->suggest('Email', ['name', 'email']);

    expect($suggestion?->field)->toBe('email')
        ->and($suggestion?->confidence)->toBe(1.0)
        ->and($suggestion?->strategy)->toBe('exact');
});

it('returns null when no field matches exactly', function () {
    expect((new ExactMatch)->suggest('e-mail', ['name', 'email']))->toBeNull();
});
