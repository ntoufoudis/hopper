<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;

final class RecordingAuditDriver implements AuditDriver
{
    /** @var list<ImportEvent> */
    public array $events = [];

    public function record(ImportEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_map(static fn (ImportEvent $event): string => $event->name, $this->events);
    }
}
