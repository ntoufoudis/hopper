<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Pipes;

use Closure;
use Ntoufoudis\Hopper\Contracts\Pipe;

final class LowercaseEmail implements Pipe
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function handle(array $row, Closure $next): array
    {
        if (isset($row['email']) && is_string($row['email'])) {
            $row['email'] = strtolower($row['email']);
        }

        return $next($row);
    }
}
