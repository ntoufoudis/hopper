<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Audit;

use Chronicle\Facades\Chronicle;
use Ntoufoudis\Hopper\Contracts\AuditDriver;

/**
 * Forwards each ImportEvent to Chronicle for tamper-evident, signed import
 * history. Soft dependency: this class is only instantiated when
 * laravel-chronicle/core is installed and hopper.audit.driver === "chronicle".
 */
final class ChronicleAuditDriver implements AuditDriver
{
    public function record(ImportEvent $event): void
    {
        Chronicle::record($event->name, array_merge($event->context, [
            'run_id' => $event->runId,
        ]));
    }
}
