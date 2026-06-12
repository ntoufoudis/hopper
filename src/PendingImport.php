<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

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

        StageChunk::dispatch($run, $this->source);

        return $run->refresh();
    }
}
