<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\CallbackResolver;
use Ntoufoudis\Hopper\Resolution\Resolution;

it('delegates the verdict to the supplied closure', function () {
    $resolver = new CallbackResolver(
        fn (array $row): Resolution => ($row['email'] ?? null) === null
            ? new Resolution(ResolutionType::Skip, reason: 'no email')
            : new Resolution(ResolutionType::Insert),
    );

    expect($resolver->resolve(['email' => 'a@b.com'])->type)->toBe(ResolutionType::Insert);

    $skipped = $resolver->resolve(['name' => 'NoEmail']);
    expect($skipped->type)->toBe(ResolutionType::Skip)
        ->and($skipped->reason)->toBe('no email');
});
