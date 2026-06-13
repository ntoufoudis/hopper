<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Strategies\AiMatch;

it('is an inert premium seam that always returns null', function () {
    expect((new AiMatch)->suggest('email', ['name', 'email']))->toBeNull();
});
