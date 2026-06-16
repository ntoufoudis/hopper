<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Imports\PipelineCustomerImport;
use Ntoufoudis\Hopper\Tests\Fixtures\RecordingAuditDriver;

it('records the lifecycle events across a full import', function () {
    $spy = new RecordingAuditDriver;
    app()->instance(AuditDriver::class, $spy);

    // customers_messy.csv drives at least one rejected/invalid row through the
    // existing PipelineCustomerImport (transform + validation) fixture.
    $run = Hopper::define(PipelineCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/csv/customers_messy.csv'))
        ->stage();

    $run->preview();
    $run->commit();

    $names = $spy->names();

    expect($names)->toContain('run.created')
        ->and($names)->toContain('row.rejected')
        ->and($names)->toContain('preview.generated')
        ->and($names)->toContain('commit.started')
        ->and($names)->toContain('commit.completed');
});
