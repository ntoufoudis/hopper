<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Support\Facades\Bus;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Jobs\StageChunk;
use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Models\ImportRun;

final class PendingImport
{
    protected Source $source;

    protected ?ColumnMap $columnMap = null;

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
        $this->columnMap = $this->mapper->autoMap(
            $this->signature(),
            $this->definition::class,
            $this->source->headers(),
            $this->definition->fields(),
        );

        return $this;
    }

    /**
     * Structure-based signature over the source headers (independent of row
     * content), so different data exports with the same column layout reuse
     * the same mapping template.
     */
    public function stage(): ImportRun
    {
        $run = ImportRun::create([
            'status' => RunStatus::Pending,
            'import_definition' => $this->definition::class,
            'source_fingerprint' => $this->source->fingerprint(),
        ]);

        // Dispatch through the bus (not StageChunk::dispatch) so the job never
        // runs inside PendingDispatch::__destruct(). On the sync connection the
        // handler executes in this stack frame; on a real queue it is enqueued.
        // Driving CsvSource's Fiber from a destructor throws "Cannot switch
        // fibers in current execution context" on PHP < 8.4.
        Bus::dispatch(new StageChunk($run, $this->source, $this->columnMap));

        return $run->refresh();
    }

    protected function signature(): string
    {
        return hash('sha256', implode('|', $this->source->headers()));
    }
}
