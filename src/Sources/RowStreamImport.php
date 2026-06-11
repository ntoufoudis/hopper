<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Sources;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Internal Laravel Excel import. Excel calls collection() once per chunk;
 * each row is emitted through the closure so a Fiber bridge can pull it.
 */
final class RowStreamImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    /**
     * @param  Closure(array<string, scalar|null>): void  $emit
     */
    public function __construct(
        private Closure $emit,
        private int $chunkSize,
    ) {
        //
    }

    /**
     * @param  Collection<int, Collection<string, scalar|null>>  $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            /** @var array<string, scalar|null> $array */
            $array = $row->toArray();

            ($this->emit)($array);
        }
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }
}
