<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use LogicException;
use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Jobs\StageChunk;
use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Models\ImportRun;

final class PendingImport
{
    protected ?Source $source = null;

    protected ?ColumnMap $columnMap = null;

    protected ?Model $actor = null;

    public function __construct(
        protected ImportDefinition $definition,
        protected Mapper $mapper,
    ) {
        //
    }

    public function from(Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Attribute this run to an actor (recorded on the run and in the audit trail).
     */
    public function by(Model $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Apply an explicit, caller-supplied header -> field map.
     */
    public function map(ColumnMap $columnMap): self
    {
        $this->columnMap = $columnMap;

        return $this;
    }

    /**
     * Resolve the map template-first, then via strategies, persisting a newly
     * computed template so the next import of this layout is zero-touch.
     */
    public function autoMap(): self
    {
        if ($this->source === null) {
            throw new LogicException('Call from() before autoMap().');
        }

        $this->columnMap = $this->mapper->autoMap(
            $this->signature($this->source),
            $this->definition::class,
            $this->source->headers(),
            $this->definition->fields(),
        );

        return $this;
    }

    /**
     * Create the run, emit run.created / mapping.resolved, and dispatch staging.
     */
    public function stage(): ImportRun
    {
        if ($this->source === null) {
            throw new LogicException('Call from() before stage().');
        }

        $attributes = [
            'status' => RunStatus::Pending,
            'import_definition' => $this->definition::class,
            'source_fingerprint' => $this->source->fingerprint(),
        ];

        $context = [
            'import_definition' => $this->definition::class,
            'source_fingerprint' => $this->source->fingerprint(),
        ];

        if ($this->actor !== null) {
            $attributes['actor_type'] = $this->actor->getMorphClass();
            $attributes['actor_id'] = $this->actor->getKey();
            $context['actor_type'] = $this->actor->getMorphClass();
            $context['actor_id'] = $this->actor->getKey();
        }

        $run = ImportRun::create($attributes);

        $audit = app(AuditDriver::class);
        $audit->record(new ImportEvent('run.created', $run->id, $context));

        if ($this->columnMap !== null) {
            $audit->record(new ImportEvent('mapping.resolved', $run->id, [
                'column_map' => $this->columnMap->toArray(),
            ]));
        }

        // Dispatch through the bus (not StageChunk::dispatch) so the job never
        // runs inside PendingDispatch::__destruct(). On the sync connection the
        // handler executes in this stack frame; on a real queue it is enqueued.
        // Driving CsvSource's Fiber from a destructor throws "Cannot switch
        // fibers in current execution context" on PHP < 8.4.
        Bus::dispatch(new StageChunk($run, $this->source, $this->columnMap));

        return $run->refresh();
    }

    /**
     * Structure-based signature over the source headers (independent of row
     * content), so different data exports with the same column layout reuse the
     * same mapping template.
     */
    protected function signature(Source $source): string
    {
        return hash('sha256', implode('|', $source->headers()));
    }
}
