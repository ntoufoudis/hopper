<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Contracts;

use Closure;

/**
 * Transforms a single mapped row. Return the (possibly modified) row, or throw
 * Ntoufoudis\Hopper\Exceptions\RowRejected to drop it with a reason that lands
 * in the failed-row report. Signature matches Illuminate\Pipeline so pipes can
 * be piped through the framework's own Pipeline.
 */
interface Pipe
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function handle(array $row, Closure $next): array;
}
