<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Ntoufoudis\Hopper\Mapping\Mapper;

final class HopperManager
{
    public function __construct(
        protected Mapper $mapper,
    ) {
        //
    }

    /**
     * @param  class-string<ImportDefinition>  $definition
     */
    public function define(string $definition): PendingImport
    {
        return new PendingImport(new $definition, $this->mapper);
    }
}
