<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Audit\DatabaseAuditDriver;
use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Models\AuditEvent;

it('is the default bound driver and records events to hopper_audit', function () {
    expect(app(AuditDriver::class))->toBeInstanceOf(DatabaseAuditDriver::class);

    app(AuditDriver::class)->record(new ImportEvent('run.created', 42, ['k' => 'v']));

    $row = AuditEvent::sole();

    expect($row->event)->toBe('run.created')
        ->and($row->run_id)->toBe(42)
        ->and($row->context)->toBe(['k' => 'v'])
        ->and($row->occurred_at)->not->toBeNull();
});
