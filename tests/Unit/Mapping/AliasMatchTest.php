<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Strategies\AliasMatch;

it('matches a known alias to its field', function () {
    $strategy = new AliasMatch(['email' => ['e-mail', 'e-mail address']]);

    $suggestion = $strategy->suggest('E-Mail Address', ['name', 'email']);

    expect($suggestion?->field)->toBe('email')
        ->and($suggestion?->confidence)->toBe(0.9)
        ->and($suggestion?->strategy)->toBe('alias');
});

it('ignores aliases whose field is not among the target fields', function () {
    $strategy = new AliasMatch(['email' => ['e-mail']]);

    expect($strategy->suggest('e-mail', ['name']))->toBeNull();
});

it('returns null for an unknown header', function () {
    $strategy = new AliasMatch(['email' => ['e-mail']]);

    expect($strategy->suggest('phone', ['name', 'email']))->toBeNull();
});
