<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Jobs\CommitChunk;

/**
 * @property int $id
 * @property RunStatus $status
 * @property string $import_definition
 * @property string $source_fingerprint
 * @property ?int $total
 * @property int $processed
 * @property int $inserted
 * @property int $updated
 * @property int $skipped
 * @property int $failed
 */
#[Fillable([
    'status',
    'import_definition',
    'source_fingerprint',
    'actor',
    'total',
    'processed',
    'inserted',
    'updated',
    'skipped',
    'failed',
    'started_at',
    'completed_at',
])]
final class ImportRun extends Model
{
    protected function casts(): array
    {
        return [
            'status' => RunStatus::class,
        ];
    }

    public function getTable(): string
    {
        return Config::string('hopper.tables.runs');
    }

    /**
     * @return array{processed: int, total: int, percentage: int}
     */
    public function progress(): array
    {
        $total = (int) ($this->total ?? 0);
        $processed = (int) $this->processed;

        return [
            'processed' => $processed,
            'total' => $total,
            'percentage' => $total > 0 ? (int) round($processed / $total * 100) : 0,
        ];
    }

    public function commit(): self
    {
        $this->update(['status' => RunStatus::Importing]);

        CommitChunk::dispatch($this);

        return $this;
    }
}
