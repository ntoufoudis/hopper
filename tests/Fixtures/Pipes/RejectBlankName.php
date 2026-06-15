<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Pipes;

use Closure;
use Ntoufoudis\Hopper\Contracts\Pipe;
use Ntoufoudis\Hopper\Exceptions\RowRejected;

final class RejectBlankName implements Pipe
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function handle(array $row, Closure $next): array
    {
        if (($row['name'] ?? '') === '') {
            throw new RowRejected('name is required');
        }

        return $next($row);
    }
}
