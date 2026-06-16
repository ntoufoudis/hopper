<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Date;
use JsonException;
use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\Exceptions\RowRejected;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Pipeline\PipeRunner;
use Ntoufoudis\Hopper\Resolution\DatabaseResolver;

final class StagingWriter
{
    public function __construct(
        protected PipeRunner $pipes,
        protected ValidationFactory $validator,
    ) {
        //
    }

    /**
     * Stream the source, applying map -> transform -> validate -> resolve ->
     * stage per row. Rejected (RowRejected) and invalid rows are diverted to
     * the failed-row store and never staged. Rows are buffered into chunks so a
     * batch-aware resolver can be primed once per chunk.
     *
     * @throws JsonException
     */
    public function write(ImportRun $run, Source $source, ImportDefinition $definition, ?ColumnMap $map = null): void
    {
        $resolver = $definition->resolver();
        $rules = $definition->rules();
        $pipes = $definition->pipes();
        $modelClass = $definition->model();
        $fingerprint = $source->fingerprint();
        $chunkSize = $definition->chunkSize();

        if ($resolver instanceof DatabaseResolver) {
            $resolver->useModel($modelClass);
        }

        /** @var list<string> $fillable */
        $fillable = array_values((new $modelClass)->getFillable());

        /** @var list<array{number: int, row: array<string, mixed>}> $chunk */
        $chunk = [];

        foreach ($source->rows() as $rowNumber => $row) {
            if ($map !== null) {
                $row = $this->applyMap($row, $map);
            }

            try {
                $row = $this->pipes->process($row, $pipes);
            } catch (RowRejected $e) {
                $this->divert($run, $fingerprint, $rowNumber, $row, $e->reason());

                continue;
            }

            $messages = $this->validate($row, $rules);

            if ($messages !== null) {
                $this->divert($run, $fingerprint, $rowNumber, $row, $messages);

                continue;
            }

            $chunk[] = ['number' => $rowNumber, 'row' => $row];

            if (count($chunk) >= $chunkSize) {
                $this->stageChunk($run, $resolver, $fillable, $fingerprint, $chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $this->stageChunk($run, $resolver, $fillable, $fingerprint, $chunk);
        }

        $run->update([
            'total' => StagingRow::where('run_id', $run->id)->count(),
            'failed' => FailedRow::where('run_id', $run->id)->count(),
        ]);
    }

    /**
     * Resolve and stage one buffered chunk. Batch-aware resolvers are primed
     * with the whole chunk first (one keyed lookup), then each row's verdict is
     * upserted into staging keyed on its unique row_hash.
     *
     * @param  list<string>  $fillable
     * @param  list<array{number: int, row: array<string, mixed>}>  $chunk
     *
     * @throws JsonException
     */
    protected function stageChunk(
        ImportRun $run,
        Resolver $resolver,
        array $fillable,
        string $fingerprint,
        array $chunk,
    ): void {
        if ($resolver instanceof DatabaseResolver) {
            $resolver->prime(array_map(static fn (array $item): array => $item['row'], $chunk));
        }

        $now = Date::now();
        $records = [];

        foreach ($chunk as $item) {
            $resolution = $resolver->resolve($item['row']);

            if ($resolution->model !== null) {
                $attributes = $resolution->model->getAttributes();
                $payload = $fillable === []
                    ? $attributes
                    : array_intersect_key($attributes, array_flip($fillable));
            } else {
                $payload = $item['row'];
            }

            $records[] = [
                'run_id' => $run->id,
                'source_row_number' => $item['number'],
                'row_hash' => hash('sha256', $run->id.':'.$fingerprint.':'.$item['number']),
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'resolution' => $resolution->type->value,
                'resolved_key' => $resolution->model?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        StagingRow::upsert(
            $records,
            ['row_hash'],
            ['run_id', 'source_row_number', 'payload', 'resolution', 'resolved_key', 'updated_at'],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $rules
     * @return string|null null when valid; otherwise the joined messages
     */
    protected function validate(array $row, array $rules): ?string
    {
        if ($rules === []) {
            return null;
        }

        $validator = $this->validator->make($row, $rules);

        if (! $validator->fails()) {
            return null;
        }

        return implode('; ', $validator->errors()->all());
    }

    /**
     * Record a dropped row against the run. Upserts on the unique row_hash so a
     * re-stage of the same file never duplicates failed rows.
     *
     * @param  array<string, mixed>  $row
     *
     * @throws JsonException
     */
    protected function divert(
        ImportRun $run,
        string $fingerprint,
        int $rowNumber,
        array $row,
        string $reason,
    ): void {
        $now = Date::now();

        FailedRow::upsert(
            [[
                'run_id' => $run->id,
                'source_row_number' => $rowNumber,
                'row_hash' => hash('sha256', $run->id.':'.$fingerprint.':'.$rowNumber),
                'payload' => json_encode($row, JSON_THROW_ON_ERROR),
                'reason' => $reason,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['row_hash'],
            ['run_id', 'source_row_number', 'payload', 'reason', 'updated_at'],
        );

        app(AuditDriver::class)->record(new ImportEvent('row.rejected', $run->id, [
            'source_row_number' => $rowNumber,
            'reason' => $reason,
        ]));
    }

    /**
     * Rewrite a row's keys from source header to target field, dropping any
     * header the map does not cover.
     *
     * @param  array<string, scalar|null>  $row
     * @return array<string, scalar|null>
     */
    protected function applyMap(array $row, ColumnMap $map): array
    {
        $mapped = [];

        foreach ($map as $header => $field) {
            if (array_key_exists($header, $row)) {
                $mapped[$field] = $row[$header];
            }
        }

        return $mapped;
    }
}
