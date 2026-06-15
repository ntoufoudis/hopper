<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Staging\Committer;
use Throwable;

final class CommitChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ImportRun $run,
    ) {
        //
    }

    /**
     * @throws Throwable
     */
    public function handle(Committer $committer): void
    {
        $committer->commit($this->run);
    }
}
