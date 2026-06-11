<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Contracts;

use Ntoufoudis\Hopper\Resolution\Resolution;

/**
 * Decides what a transformed row becomes against the target table.
 */
interface Resolver
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function resolve(array $row): Resolution;
}
