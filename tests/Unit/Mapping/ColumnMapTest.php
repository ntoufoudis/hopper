<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\ColumnMap;

it('resolves a target field for a header and returns null for unknown headers', function () {
    $map = new ColumnMap(['Full Name' => 'name', 'E-Mail Address' => 'email']);

    expect($map->field('Full Name'))->toBe('name')
        ->and($map->field('E-Mail Address'))->toBe('email')
        ->and($map->field('Unknown'))->toBeNull();
});

it('exposes the map as an array and is iterable as header => field', function () {
    $map = new ColumnMap(['Full Name' => 'name']);

    expect($map->toArray())->toBe(['Full Name' => 'name']);

    $pairs = [];
    foreach ($map as $header => $field) {
        $pairs[$header] = $field;
    }

    expect($pairs)->toBe(['Full Name' => 'name']);
});
