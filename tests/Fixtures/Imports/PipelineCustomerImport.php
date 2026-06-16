<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Imports;

use Ntoufoudis\Hopper\Contracts\Pipe;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;
use Ntoufoudis\Hopper\Tests\Fixtures\Pipes\LowercaseEmail;
use Ntoufoudis\Hopper\Tests\Fixtures\Pipes\RejectBlankName;

final class PipelineCustomerImport extends ImportDefinition
{
    public function model(): string
    {
        return Customer::class;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['email' => 'required|email'];
    }

    /** @return Pipe */
    public function pipes(): array
    {
        // RejectBlankName runs first so a blank name is dropped during transform,
        // before validation; LowercaseEmail normalises the address pre-validation.
        return [new RejectBlankName, new LowercaseEmail];
    }

    // Small chunk so chunk buffering is observable.
    public function chunkSize(): int
    {
        return 2;
    }
}
