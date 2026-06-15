<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Exceptions\RowRejected;

it('carries a reason exposed via reason() and getMessage()', function () {
    $e = new RowRejected('email is required');

    expect($e)->toBeInstanceOf(RuntimeException::class)
        ->and($e->reason())->toBe('email is required')
        ->and($e->getMessage())->toBe('email is required');
});
