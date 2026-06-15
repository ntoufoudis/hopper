<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Resolution\UpsertResolver;

final class UpsertCustomerImport extends ImportDefinition
{
    public function model(): string
    {
        return Customer::class;
    }

    public function resolver(): Resolver
    {
        return UpsertResolver::by('email');
    }

    // Small chunk so chunk count (and therefore prime-query count) is observable.
    public function chunkSize(): int
    {
        return 2;
    }
}
