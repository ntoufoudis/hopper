<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Imports;

use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\BrokenModel;

final class BrokenImport extends ImportDefinition
{
    public function model(): string
    {
        return BrokenModel::class;
    }
}
