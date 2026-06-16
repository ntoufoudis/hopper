<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\CustomerImport;

it('throws a clear error when staging before from()', function () {
    expect(fn () => Hopper::define(CustomerImport::class)->stage())
        ->toThrow(LogicException::class);
});

it('throws a clear error when auto-mapping before from()', function () {
    expect(fn () => Hopper::define(CustomerImport::class)->autoMap())
        ->toThrow(LogicException::class);
});
