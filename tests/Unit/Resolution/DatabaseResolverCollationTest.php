<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Resolution\DatabaseResolver;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

function foldResolver(): DatabaseResolver
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

it('matches case-insensitively as a fallback', function () {
    Customer::create(['name' => 'Alice', 'email' => 'Alice@X.com']);

    $resolver = foldResolver();
    $resolver->useModel(Customer::class);
    $resolver->prime([['email' => 'Alice@X.com']]); // loaded exactly under sqlite's binary collation

    // Incoming differs only in case: exact lookup misses, fold fallback hits.
    expect($resolver->resolve(['email' => 'alice@x.com'])->type)->toBe(ResolutionType::Update);
});

it('does not fold-match when the folded key is ambiguous', function () {
    Customer::create(['name' => 'A', 'email' => 'A@x.com']);
    Customer::create(['name' => 'B', 'email' => 'a@x.com']);

    $resolver = foldResolver();
    $resolver->useModel(Customer::class);
    $resolver->prime([['email' => 'A@x.com'], ['email' => 'a@x.com']]);

    // Exact case still resolves to Update.
    expect($resolver->resolve(['email' => 'A@x.com'])->type)->toBe(ResolutionType::Update)
        // A new casing whose fold ('a@x.com') is ambiguous must NOT fold-match.
        ->and($resolver->resolve(['email' => 'A@X.com'])->type)->toBe(ResolutionType::Insert);

});
