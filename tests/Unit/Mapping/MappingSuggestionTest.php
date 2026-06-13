<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

it('holds field, confidence, and strategy', function () {
    $suggestion = new MappingSuggestion('email', 0.9, 'alias');

    expect($suggestion->field)->toBe('email')
        ->and($suggestion->confidence)->toBe(0.9)
        ->and($suggestion->strategy)->toBe('alias');
});
