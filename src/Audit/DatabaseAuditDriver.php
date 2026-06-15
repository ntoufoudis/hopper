<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Audit;

use Illuminate\Support\Facades\Date;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Models\AuditEvent;

final class DatabaseAuditDriver implements AuditDriver
{
    public function record(ImportEvent $event): void
    {
        AuditEvent::create([
            'run_id' => $event->runId,
            'event' => $event->name,
            'context' => $event->context,
            'occurred_at' => Date::now(),
        ]);
    }
}
