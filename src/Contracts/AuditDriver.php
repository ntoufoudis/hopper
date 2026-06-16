<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Contracts;

use Ntoufoudis\Hopper\Audit\ImportEvent;

/**
 * Sink for import lifecycle events. The default DatabaseAuditDriver writes to
 * hopper_audit.
 */
interface AuditDriver
{
    public function record(ImportEvent $event): void;
}
