<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Support\Facades\Facade;

/**
 * @method static PendingImport define($definition)
 *
 * @see HopperManager
 */
final class Hopper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HopperManager::class;
    }
}
