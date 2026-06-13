<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Mapping\Mapper;
use Ntoufoudis\Hopper\Mapping\Strategies\AiMatch;
use Ntoufoudis\Hopper\Mapping\Strategies\AliasMatch;
use Ntoufoudis\Hopper\Mapping\Strategies\ExactMatch;
use Ntoufoudis\Hopper\Mapping\Strategies\FuzzyMatch;

function testMapper(): Mapper
{
    return new Mapper([
        new ExactMatch,
        new AliasMatch(['email' => ['e-mail']]),
        new FuzzyMatch(0.8),
    ]);
}

it('takes the first non-null suggestion in priority order', function () {
    // "Email" matches exactly, so exact wins before alias/fuzzy are consulted.
    $suggestions = testMapper()->suggestions(['Email'], ['name', 'email']);

    expect($suggestions['Email']->strategy)->toBe('exact');
});

it('falls back to alias then fuzzy when exact misses', function () {
    $suggestions = testMapper()->suggestions(['e-mail', 'Namee'], ['name', 'email']);

    expect($suggestions['e-mail']->strategy)->toBe('alias')
        ->and($suggestions['e-mail']->field)->toBe('email')
        ->and($suggestions['Namee']->strategy)->toBe('fuzzy')
        ->and($suggestions['Namee']->field)->toBe('name');
});

it('omits headers no strategy can place', function () {
    $suggestions = testMapper()->suggestions(['Telephone'], ['name', 'email']);

    expect($suggestions)->toBe([]);
});

it('assembles a ColumnMap of header => field', function () {
    $map = testMapper()->strategyMap(['Email'], ['name', 'email']);

    expect($map->toArray())->toBe(['Email' => 'email']);
});

it('binds a default chain of Exact, Alias, Fuzzy without AiMatch', function () {
    $mapper = app(Mapper::class);

    $reflection = new ReflectionProperty(Mapper::class, 'strategies');
    $strategies = $reflection->getValue($mapper);
    $types = array_map(fn ($s) => $s::class, $strategies);

    expect($types)->toContain(ExactMatch::class)
        ->and($types)->toContain(AliasMatch::class)
        ->and($types)->toContain(FuzzyMatch::class)
        ->and($types)->not->toContain(AiMatch::class);
});
