<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Support\Facades\Bus;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Jobs\StageChunk;
use Ntoufoudis\Hopper\Models\ImportRun;

final class PendingImport
{
    protected Source $source;

    public function __construct(
        protected ImportDefinition $definition,
    ) {
        //
    }

    public function from(Source $source): self
    {
        $this->source = $source;

        return $this;
    }

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
        Bus::dispatch(new StageChunk($run, $this->source));

        return $run->refresh();
    }
}
