<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\InsertOnlyResolver;

it('resolves every row to an insert', function () {
    $resolver = new InsertOnlyResolver;

    $resolution = $resolver->resolve(['name' => 'Alice', 'email' => 'alice@example.com']);

    expect($resolution->type)->toBe(ResolutionType::Insert)
        ->and($resolution->model)->toBeNull();
});
