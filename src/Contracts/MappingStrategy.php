<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Contracts;

use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

/**
 * Proposes a target field for one source header. Strategies run in priority
 * order (exact -> alias -> fuzzy); the first non-null suggestion wins. ExactMatch,
 * AliasMatch, and FuzzyMatch ship in v1; AiMatch is a registered-but-empty seam
 * for the premium tier (returns null, not wired into the default chain).
 */
interface MappingStrategy
{
    /**
     * @param  list<string>  $targetFields
     */
    public function suggest(string $header, array $targetFields): ?MappingSuggestion;
}
