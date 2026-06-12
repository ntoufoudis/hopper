<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

use Illuminate\Support\Facades\Date;
use JsonException;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;

final class StagingWriter
{
    /**
     * @throws JsonException
     */
    public function write(ImportRun $run, Source $source, ImportDefinition $definition): void
    {
        $resolver = $definition->resolver();
        $fingerprint = $source->fingerprint();
        $chunkSize = $definition->chunkSize();

        /** @var list<array<string, mixed>> $buffer */
        $buffer = [];

        foreach ($source->rows() as $rowNumber => $row) {
            $resolution = $resolver->resolve($row);
            $now = Date::now();

            $buffer[] = [
                'run_id' => $run->id,
                'source_row_number' => $rowNumber,
                'row_hash' => hash('sha256', $fingerprint.':'.$rowNumber),
                'payload' => json_encode($row, JSON_THROW_ON_ERROR),
                'resolution' => $resolution->type->value,
                'resolved_key' => $resolution->model?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($buffer) >= $chunkSize) {
                $this->flush($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            $this->flush($buffer);
        }

        $run->update(['total' => StagingRow::where('run_id', $run->id)->count()]);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function flush(array $records): void
    {
        // Upsert on the unique row_hash so re-staging never duplicates and never
        // clobbers an already-stamped committed_at.
        StagingRow::upsert(
            $records,
            ['row_hash'],
            ['run_id', 'source_row_number', 'payload', 'resolution', 'resolved_key', 'updated_at'],
        );
    }
}
