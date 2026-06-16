<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\ImportDefinition;

final class BrokenImport extends ImportDefinition
{
    public function model(): string
    {
        return BrokenModel::class;
    }
}
