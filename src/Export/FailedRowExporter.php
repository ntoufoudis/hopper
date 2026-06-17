<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Export;

use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;
use RuntimeException;

/**
 * Renders a run's failed rows as CSV: the union of payload columns plus a final
 * "error" column. Cell values beginning with =, +, -, or @ are prefixed with a
 * tab to neutralise spreadsheet formula injection (mirrors Filament's guard).
 */
final class FailedRowExporter
{
    public function export(ImportRun $run): string
    {
        $rows = FailedRow::query()
            ->where('run_id', $run->id)
            ->orderBy('source_row_number')
            ->get();

        /** @var array<string, true> $columnSet */
        $columnSet = [];

        foreach ($rows as $row) {
            foreach (array_keys($row->payload) as $key) {
                $columnSet[(string) $key] = true;
            }
        }

        $columns = array_keys($columnSet);

        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Unable to open a temporary stream for the failed-row export.');
        }

        $header = array_map(fn (string $column): string => $this->guard($column), $columns);
        $header[] = 'error';
        fputcsv($handle, $header, escape: '');

        foreach ($rows as $row) {
            $line = [];

            foreach ($columns as $column) {
                $value = $row->payload[$column] ?? '';
                $line[] = $this->guard(is_scalar($value) ? (string) $value : '');
            }

            $line[] = $this->guard($row->reason);
            fputcsv($handle, $line, escape: '');
        }

        rewind($handle);

        $contents = stream_get_contents($handle);
        fclose($handle);

        return $contents === false ? '' : $contents;
    }

    /**
     * Prefix a cell with a tab when it starts with a formula trigger character.
     */
    protected function guard(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "\t".$value;
        }

        return $value;
    }
}
