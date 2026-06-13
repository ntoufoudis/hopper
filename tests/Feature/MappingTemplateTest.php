<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\ColumnMap;
use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Models\MappingTemplate;

it('persists a new template the first time a signature is auto-mapped', function () {
    $map = app(Mapper::class)->autoMap('sig-1', 'App\\FooImport', ['Email'], ['name', 'email']);

    expect($map->toArray())->toBe(['Email' => 'email'])
        ->and(MappingTemplate::count())->toBe(1);

    $template = MappingTemplate::first();
    expect($template->source_signature)->toBe('sig-1')
        ->and($template->import_definition)->toBe('App\\FooImport')
        ->and($template->column_map)->toBe(['Email' => 'email']);
});

it('reuses a saved template instead of re-running strategies', function () {
    app(Mapper::class)->saveTemplate('sig-1', 'App\\FooImport', new ColumnMap(['Email' => 'name']));

    // Strategies would map "Email" => "email"; the template must win instead.
    $map = app(Mapper::class)->autoMap('sig-1', 'App\\FooImport', ['Email'], ['name', 'email']);

    expect($map->toArray())->toBe(['Email' => 'name'])
        ->and(MappingTemplate::count())->toBe(1);
});

it('scopes templates by import definition as well as signature', function () {
    app(Mapper::class)->saveTemplate('sig-1', 'App\\FooImport', new ColumnMap(['Email' => 'email']));
    app(Mapper::class)->saveTemplate('sig-1', 'App\\BarImport', new ColumnMap(['Email' => 'email']));

    expect(MappingTemplate::count())->toBe(2);
});
