<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Models\MappingTemplate;

it('does not persist a template when no header maps to a field', function () {
    /** @var Mapper $mapper */
    $mapper = app(Mapper::class);

    $map = $mapper->autoMap(
        signature: 'sig-unmatched',
        definition: 'App\\Imports\\X',
        headers: ['zzz_unknown', 'qqq_unknown'],
        targetFields: ['name', 'email'],
    );

    expect($map->toArray())->toBe([])
        ->and(MappingTemplate::count())->toBe(0);
});
