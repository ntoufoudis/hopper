<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\UpsertResolver;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('resolves a new key to Insert', function () {
    $resolver = UpsertResolver::by('email');
    $resolver->useModel(Customer::class);
    $resolver->prime([['name' => 'Alice', 'email' => 'alice@example.com']]);

    $resolution = $resolver->resolve(['name' => 'Alice', 'email' => 'alice@example.com']);

    expect($resolution->type)->toBe(ResolutionType::Insert)
        ->and($resolution->model)->toBeNull();
});

it('resolves an existing key to Update carrying the existing model overwritten with incoming', function () {
    $existing = Customer::create(['name' => 'Old Name', 'email' => 'alice@example.com']);

    $resolver = UpsertResolver::by('email');
    $resolver->useModel(Customer::class);
    $resolver->prime([['name' => 'Alice', 'email' => 'alice@example.com']]);

    $resolution = $resolver->resolve(['name' => 'Alice', 'email' => 'alice@example.com']);

    expect($resolution->type)->toBe(ResolutionType::Update)
        ->and($resolution->model?->getKey())->toBe($existing->getKey())
        ->and($resolution->model?->getAttribute('name'))->toBe('Alice')
        ->and($resolution->model?->getAttribute('email'))->toBe('alice@example.com');
});
