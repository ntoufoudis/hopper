<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

final class HopperManager
{
    /**
     * @param  class-string<ImportDefinition>  $definition
     */
    public function define(string $definition): PendingImport
    {
        return new PendingImport(new $definition);
    }
}
