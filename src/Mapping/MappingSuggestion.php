<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping;

final readonly class MappingSuggestion
{
    public function __construct(
        public string $field,
        public float $confidence,
        public string $strategy,
    ) {
        //
    }
}
