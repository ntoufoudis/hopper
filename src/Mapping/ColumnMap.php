<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * The resolved source-header -> target-field map for one run. Persisted as a
 * MappingTemplate keyed by source signature so the second import of the same
 * vendor file is zero-touch.
 *
 * @implements IteratorAggregate<string, string>
 */
final class ColumnMap implements IteratorAggregate
{
    /**
     * @param  array<string, string>  $map
     */
    public function __construct(
        private array $map,
    ) {
        //
    }

    public function field(string $header): ?string
    {
        return $this->map[$header] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * @return Traversable<string, string>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->map);
    }
}
