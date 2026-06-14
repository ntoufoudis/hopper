<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Ntoufoudis\Hopper\Contracts\Source;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Staging\StagingWriter;

final class StageChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ImportRun $run,
        public Source $source,
        public ?ColumnMap $columnMap = null,
    ) {
        //
    }

    /**
     * @throws JsonException
     */
    public function handle(StagingWriter $writer): void
    {
        $this->run->update(['status' => RunStatus::Staging]);

        /** @var ImportDefinition $definition */
        $definition = new ($this->run->import_definition);

        $writer->write($this->run, $this->source, $definition, $this->columnMap);

        $this->run->update(['status' => RunStatus::Ready]);
    }
}
