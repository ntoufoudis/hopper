<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Jobs\CommitChunk;
use Ntoufoudis\Hopper\Staging\ImportPreview;
use Ntoufoudis\Hopper\Staging\PreviewBuilder;

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
 * @property ?Carbon $started_at
 * @property ?Carbon $completed_at
 */
#[Fillable([
    'status',
    'import_definition',
    'source_fingerprint',
    'actor_type',
    'actor_id',
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

    /**
     * @throws CircularDependencyException
     * @throws BindingResolutionException
     */
    public function preview(): ImportPreview
    {
        return app(PreviewBuilder::class)->build($this);
    }

    public function commit(): self
    {
        $this->update(['status' => RunStatus::Importing]);

        // Dispatch through the bus (not CommitChunk::dispatch) so the job never
        // runs inside PendingDispatch::__destruct(): on the sync connection a
        // rethrown commit failure must surface here, not as a fatal exception
        // thrown from a destructor. Mirrors PendingImport::stage().
        Bus::dispatch(new CommitChunk($this));

        return $this;
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
