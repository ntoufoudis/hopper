<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\ImportDefinition;

final class CustomerImport extends ImportDefinition
{
    public function model(): string
    {
        return Customer::class;
    }

    // Small chunk size so chunking/resume behaviour is observable in tests.
    public function chunkSize(): int
    {
        return 2;
    }
}
