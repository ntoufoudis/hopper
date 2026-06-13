<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping\Strategies;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;
use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

/**
 * Premium seam. Implements the contract but is intentionally NOT wired into the
 * default Mapper chain and always returns null, so the pipeline behaves exactly
 * as if it were absent until a paid implementation replaces it.
 */
final class AiMatch implements MappingStrategy
{
    public function suggest(string $header, array $targetFields): ?MappingSuggestion
    {
        return null;
    }
}
