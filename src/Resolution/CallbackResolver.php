<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Closure;
use Ntoufoudis\Hopper\Contracts\Resolver;

/**
 * Escape hatch: resolves each row with a caller-supplied closure. Not batch
 * aware (the closure owns any lookups it needs), so it is never primed.
 */
final class CallbackResolver implements Resolver
{
    /**
     * @param  Closure(array<string, mixed>): Resolution  $callback
     */
    public function __construct(
        protected Closure $callback,
    ) {
        //
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolve(array $row): Resolution
    {
        return ($this->callback)($row);
    }
}
