<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\DatabaseResolver;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\ArrayEmailCustomer;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

/** A minimal concrete resolver that overwrites the matched model with incoming. */
function inlineResolver(): DatabaseResolver
{
    return new class('email') extends DatabaseResolver
    {
        protected function applyUpdate(Model $existing, array $row): Model
        {
            $existing->fill($row);

            return $existing;
        }
    };
}

it('resolves a new field value to Insert and an existing one to Update', function () {
    $existing = Customer::create(['name' => 'Old', 'email' => 'a@b.com']);

    $resolver = inlineResolver();
    $resolver->useModel(Customer::class);
    $resolver->prime([
        ['name' => 'Alice', 'email' => 'a@b.com'],
        ['name' => 'New', 'email' => 'new@b.com'],
    ]);

    $update = $resolver->resolve(['name' => 'Alice', 'email' => 'a@b.com']);
    $insert = $resolver->resolve(['name' => 'New', 'email' => 'new@b.com']);

    expect($update->type)->toBe(ResolutionType::Update)
        ->and($update->model?->getKey())->toBe($existing->getKey())
        ->and($insert->type)->toBe(ResolutionType::Insert)
        ->and($insert->model)->toBeNull();
});

it('primes a chunk with exactly one query', function () {
    Customer::create(['name' => 'Old', 'email' => 'a@b.com']);

    $resolver = inlineResolver();
    $resolver->useModel(Customer::class);

    $selects = 0;
    Event::listen(function (QueryExecuted $event) use (&$selects): void {
        if (str_contains($event->sql, 'customers')) {
            $selects++;
        }
    });

    $resolver->prime([
        ['email' => 'a@b.com'],
        ['email' => 'b@b.com'],
        ['email' => 'c@b.com'],
    ]);

    expect($selects)->toBe(1);
});

it('inserts everything and never queries when no model is set', function () {
    $resolver = inlineResolver();

    $selects = 0;
    Event::listen(function (QueryExecuted $event) use (&$selects): void {
        $selects++;
    });

    $resolver->prime([['email' => 'a@b.com']]);

    expect($resolver->resolve(['email' => 'a@b.com'])->type)->toBe(ResolutionType::Insert)
        ->and($selects)->toBe(0);
});

it('skips a found record whose match-field value is non-scalar (falls back to Insert)', function () {
    // Stored as a plain string, but read back through a model that casts email
    // to an array, so the primed lookup sees a non-scalar value and skips it.
    Customer::create(['name' => 'Alice', 'email' => 'alice@example.com']);

    $resolver = inlineResolver();
    $resolver->useModel(ArrayEmailCustomer::class);
    $resolver->prime([['email' => 'alice@example.com']]);

    expect($resolver->resolve(['email' => 'alice@example.com'])->type)->toBe(ResolutionType::Insert);
});

it('treats a blank or missing match field as no match (Insert)', function () {
    Customer::create(['name' => 'Old', 'email' => 'a@b.com']);

    $resolver = inlineResolver();
    $resolver->useModel(Customer::class);
    $resolver->prime([['name' => 'NoEmail'], ['email' => '']]);

    expect($resolver->resolve(['name' => 'NoEmail'])->type)->toBe(ResolutionType::Insert)
        ->and($resolver->resolve(['email' => ''])->type)->toBe(ResolutionType::Insert);
});
