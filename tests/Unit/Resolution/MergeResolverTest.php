<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\MergeResolver;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('merges incoming over existing but preserves existing where incoming is blank', function () {
    $existing = Customer::create(['name' => 'Original Name', 'email' => 'alice@example.com']);

    $resolver = MergeResolver::by('email');
    $resolver->useModel(Customer::class);

    // Incoming has a blank name -> existing name must survive the merge.
    $incoming = ['name' => '', 'email' => 'alice@example.com'];
    $resolver->prime([$incoming]);

    $resolution = $resolver->resolve($incoming);

    expect($resolution->type)->toBe(ResolutionType::Update)
        ->and($resolution->model?->getKey())->toBe($existing->getKey())
        ->and($resolution->model?->getAttribute('name'))->toBe('Original Name')
        ->and($resolution->model?->getAttribute('email'))->toBe('alice@example.com');
});

it('lets a non-blank incoming value win over existing', function () {
    Customer::create(['name' => 'Original Name', 'email' => 'alice@example.com']);

    $resolver = MergeResolver::by('email');
    $resolver->useModel(Customer::class);

    $incoming = ['name' => 'New Name', 'email' => 'alice@example.com'];
    $resolver->prime([$incoming]);

    expect($resolver->resolve($incoming)->model?->getAttribute('name'))->toBe('New Name');
});

it('inserts when there is no match', function () {
    $resolver = MergeResolver::by('email');
    $resolver->useModel(Customer::class);
    $resolver->prime([['name' => 'Zoe', 'email' => 'zoe@example.com']]);

    expect($resolver->resolve(['name' => 'Zoe', 'email' => 'zoe@example.com'])->type)
        ->toBe(ResolutionType::Insert);
});
