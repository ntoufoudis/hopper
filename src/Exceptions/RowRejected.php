<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Exceptions;

use RuntimeException;

/**
 * Thrown from within a Pipe to drop the current row from staging. The reason is
 * recorded against the row in the failed-row store.
 */
final class RowRejected extends RuntimeException
{
    public function __construct(string $reason)
    {
        parent::__construct($reason);
    }

    public function reason(): string
    {
        return $this->getMessage();
    }
}
