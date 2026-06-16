<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Audit\ImportEvent;

it('carries a name, optional run id, and context', function () {
    $event = new ImportEvent('row.rejected', 7, ['reason' => 'bad']);

    expect($event->name)->toBe('row.rejected')
        ->and($event->runId)->toBe(7)
        ->and($event->context)->toBe(['reason' => 'bad']);

    $bare = new ImportEvent('preview.generated');

    expect($bare->runId)->toBeNull()
        ->and($bare->context)->toBe([]);
});
