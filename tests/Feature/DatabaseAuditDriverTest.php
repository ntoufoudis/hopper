<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Audit\DatabaseAuditDriver;
use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\AuditEvent;
use Ntoufoudis\Hopper\Models\ImportRun;

it('is the default bound driver and records events to hopper_audit', function () {
    expect(app(AuditDriver::class))->toBeInstanceOf(DatabaseAuditDriver::class);

    $run = ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);

    app(AuditDriver::class)->record(new ImportEvent('run.created', $run->id, ['k' => 'v']));

    $row = AuditEvent::sole();

    expect($row->event)->toBe('run.created')
        ->and($row->run_id)->toBe($run->id)
        ->and($row->context)->toBe(['k' => 'v'])
        ->and($row->occurred_at)->not->toBeNull();
});
