<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\ImportDefinition;

final class ExplodingImport extends ImportDefinition
{
    public function model(): string
    {
        return ExplodingCustomer::class;
    }

    // Chunk size 2 so the first chunk commits before the BOOM row in chunk 2.
    public function chunkSize(): int
    {
        return 2;
    }
}
