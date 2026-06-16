<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Tests\Fixtures\Models\Customer;

it('defaults chunkSize to the configured default_chunk_size', function () {
    config()->set('hopper.default_chunk_size', 250);

    $definition = new class extends ImportDefinition
    {
        public function model(): string
        {
            return Customer::class;
        }
    };

    expect($definition->chunkSize())->toBe(250);
});
