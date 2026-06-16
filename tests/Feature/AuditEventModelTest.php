<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\AuditEvent;
use Ntoufoudis\Hopper\Models\ImportRun;

it('persists an audit event with cast context and resolves the configured table', function () {
    $run = ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);

    $event = AuditEvent::create([
        'run_id' => $run->id,
        'event' => 'run.created',
        'context' => ['import_definition' => 'App\\X'],
        'occurred_at' => now(),
    ]);

    expect($event->getTable())->toBe('hopper_audit')
        ->and($event->fresh()->context)->toBe(['import_definition' => 'App\\X'])
        ->and($event->event)->toBe('run.created');
});
