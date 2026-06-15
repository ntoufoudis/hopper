<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Sources;

use Fiber;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Ntoufoudis\Hopper\Contracts\Source;
use Throwable;

/**
 * Streams a xlsx/xls through maatwebsite/excel's chunk reader, bridged to a
 * pull-based generator via a Fiber. Identical contract to CsvSource; the reader
 * type is auto-detected from the file extension so both formats are supported.
 */
final class ExcelSource implements Source
{
    protected function __construct(
        protected string $path,
        protected int $chunkSize = 500,
    ) {
        //
    }

    public static function make(string|UploadedFile $file, int $chunkSize = 500): self
    {
        $path = $file instanceof UploadedFile ? (string) $file->getRealPath() : $file;

        return new self($path, $chunkSize);
    }

    /**
     * @return list<string>
     */
    public function headers(): array
    {
        // Read header labels verbatim; mapping keys off the original spelling.
        HeadingRowFormatter::default('none');

        // Null reader type => detect from the file extension (xlsx or xls).
        $sheets = (new HeadingRowImport)->toArray($this->path);

        $first = data_get($sheets, '0.0', []);

        if (! is_array($first)) {
            return [];
        }

        return array_values(array_map(
            static fn (mixed $value): string => is_scalar($value) ? (string) $value : '',
            $first,
        ));
    }

    /**
     * @return iterable<int, array<string, scalar|null>>
     *
     * @throws Throwable
     */
    public function rows(): iterable
    {
        // Keep row keys identical to headers() so ColumnMap lookups line up.
        HeadingRowFormatter::default('none');

        $fiber = new Fiber(function (): void {
            ExcelFacade::import(
                new RowStreamImport(
                    static function (array $row): void {
                        Fiber::suspend($row);
                    },
                    $this->chunkSize,
                ),
                $this->path,
            );
        });

        $row = $fiber->start();
        $number = 1;

        while (! $fiber->isTerminated()) {
            if (is_array($row)) {
                yield $number++ => $this->normaliseRow($row);
            }

            $row = $fiber->resume();
        }
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->path.':'.hash_file('sha256', $this->path));
    }

    /**
     * @param  array<mixed, mixed>  $row
     * @return array<string, scalar|null>
     */
    protected function normaliseRow(array $row): array
    {
        $normalised = [];

        foreach ($row as $key => $value) {
            $normalised[(string) $key] = is_scalar($value) ? $value : null;
        }

        return $normalised;
    }
}
