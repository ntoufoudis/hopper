<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

it('defaults rules() and pipes() to empty arrays', function () {
    $definition = new CustomerImport;

    expect($definition->rules())->toBe([])
        ->and($definition->pipes())->toBe([]);
});
