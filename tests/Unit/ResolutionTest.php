<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Resolution\Resolution;

it('builds a skip resolution with a reason and no model', function () {
    $resolution = new Resolution(ResolutionType::Skip, reason: 'duplicate');

    expect($resolution->type)->toBe(ResolutionType::Skip)
        ->and($resolution->model)->toBeNull()
        ->and($resolution->reason)->toBe('duplicate');
});

it('exposes the three resolution types', function () {
    expect(ResolutionType::cases())->toHaveCount(3)
        ->and(ResolutionType::Insert->value)->toBe('insert')
        ->and(ResolutionType::Update->value)->toBe('update')
        ->and(ResolutionType::Skip->value)->toBe('skip');
});

it('does not include a Mapping status in M1', function () {
    $values = array_map(fn (RunStatus $s) => $s->value, RunStatus::cases());

    expect($values)->not->toContain('mapping')
        ->and($values)->toContain('pending')
        ->and($values)->toContain('completed');
});
