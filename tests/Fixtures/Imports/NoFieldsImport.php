<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Imports;

use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\NoFieldsModel;

final class NoFieldsImport extends ImportDefinition
{
    public function model(): string
    {
        return NoFieldsModel::class;
    }
}
