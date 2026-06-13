<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping;

final readonly class MappingSuggestion
{
    public function __construct(
        public string $field,
        public float $confidence, // 0.0-1.0
        public string $strategy,  // e.g. "exact", "alias", "fuzzy
    ) {
        //
    }
}
