<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Imports;

use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Resolution\CallbackResolver;
use Ntoufoudis\Hopper\Resolution\Resolution;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

final class SkippingCustomerImport extends ImportDefinition
{
    public function model(): string
    {
        return Customer::class;
    }

    /** Skip the row named "Bob"; insert everything else. */
    public function resolver(): Resolver
    {
        return new CallbackResolver(static fn (array $row): Resolution => ($row['name'] ?? null) === 'Bob'
            ? new Resolution(ResolutionType::Skip)
            : new Resolution(ResolutionType::Insert));
    }

    // Small chunk so chunk buffering is observable.
    public function chunkSize(): int
    {
        return 2;
    }
}
