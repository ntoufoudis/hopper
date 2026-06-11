<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Contracts;

/**
 * Yields raw, untyped rows from an origin. v1 ships CsvSource (and later
 * ExcelSource), both delegating parsing to maatwebsite/excel.
 */
interface Source
{
    /**
     * Raw header labels exactly as they appear in the source, in order.
     *
     * @return list<string>
     */
    public function headers(): array;

    /**
     * Lazily stream rows keyed by header label. Implementations MUST yield
     * (generator) and never materialise the whole file in memory.
     *
     * @return iterable<int, array<string, scalar|null>>
     */
    public function rows(): iterable;

    /**
     * Stable identifier for this source (stored path + content hash).
     */
    public function fingerprint(): string;
}
