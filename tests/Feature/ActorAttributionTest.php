<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Models\AuditEvent;
use Ntoufoudis\Hopper\Sources\CsvSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Customer;
use Ntoufoudis\Hopper\Tests\Fixtures\CustomerImport;

it('records the actor that initiated the run', function () {
    $actor = Customer::create(['name' => 'Admin', 'email' => 'admin@example.com']);

    $run = Hopper::define(CustomerImport::class)
        ->by($actor)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();

    $run->refresh();

    expect($run->actor_id)->toBe($actor->getKey())
        ->and($run->actor_type)->toBe($actor->getMorphClass())
        ->and($run->actor)->not->toBeNull();

    $created = AuditEvent::where('run_id', $run->id)->where('event', 'run.created')->first();
    expect($created->context['actor_type'])->toBe($actor->getMorphClass())
        ->and($created->context['actor_id'])->toBe($actor->getKey());
});
