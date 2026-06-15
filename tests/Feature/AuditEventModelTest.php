<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Models\AuditEvent;

it('persists an audit event with cast context and resolves the configured table', function () {
    $event = AuditEvent::create([
        'run_id' => 1,
        'event' => 'run.created',
        'context' => ['import_definition' => 'App\\X'],
        'occurred_at' => now(),
    ]);

    expect($event->getTable())->toBe('hopper_audit')
        ->and($event->fresh()->context)->toBe(['import_definition' => 'App\\X'])
        ->and($event->event)->toBe('run.created');
});
