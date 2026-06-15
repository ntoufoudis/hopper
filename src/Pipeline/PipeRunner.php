<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Pipeline;

use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Ntoufoudis\Hopper\Contracts\Pipe;

final class PipeRunner
{
    public function __construct(
        protected Container $container,
    ) {
        //
    }

    /**
     * Send a row through the definition's pipes. Pipes may be class-strings
     * (resolved from the container) or instances. A pipe may throw RowRejected
     * to drop the row; that exception propagates to the caller.
     *
     * @param  array<string, mixed>  $row
     * @param  list<class-string<Pipe>|Pipe>  $pipes
     * @return array<string, mixed>
     */
    public function process(array $row, array $pipes): array
    {
        if ($pipes === []) {
            return $row;
        }

        /** @var array<string, mixed> $result */
        $result = (new Pipeline($this->container))
            ->send($row)
            ->through($pipes)
            ->thenReturn();

        return $result;
    }
}
