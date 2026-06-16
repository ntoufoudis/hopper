<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\ImportDefinition;

final class NoFieldsImport extends ImportDefinition
{
    public function model(): string
    {
        return NoFieldsModel::class;
    }
}
