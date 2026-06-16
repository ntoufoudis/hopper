<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Audit\ChronicleAuditDriver;
use Ntoufoudis\Hopper\Contracts\AuditDriver;

it('implements the AuditDriver contract', function () {
    expect(is_subclass_of(ChronicleAuditDriver::class, AuditDriver::class))->toBeTrue();
});

it('is resolved when configured and the Chronicle package is installed', function () {
    if (! class_exists('LaravelChronicle\\Core\\Facades\\Chronicle')) {
        $this->markTestSkipped('laravel-chronicle/core is not installed.');
    }

    config()->set('hopper.audit.driver', 'chronicle');
    app()->forgetInstance(AuditDriver::class);

    expect(app(AuditDriver::class))->toBeInstanceOf(ChronicleAuditDriver::class);
});

it('throws when chronicle is configured but the package is absent', function () {
    if (class_exists('LaravelChronicle\\Core\\Facades\\Chronicle')) {
        $this->markTestSkipped('laravel-chronicle/core is installed; absence path not exercisable.');
    }

    config()->set('hopper.audit.driver', 'chronicle');
    app()->forgetInstance(AuditDriver::class);

    expect(fn () => app(AuditDriver::class))->toThrow(RuntimeException::class);
});
