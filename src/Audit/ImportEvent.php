<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Audit;

/**
 * One auditable moment in an import's lifecycle. `name` is a dotted event key
 * (e.g. "run.created"); `context` is arbitrary structured metadata.
 */
final readonly class ImportEvent
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $name,
        public ?int $runId = null,
        public array $context = [],
    ) {
        //
    }
}
