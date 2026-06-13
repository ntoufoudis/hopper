<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping\Strategies;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;
use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

/**
 * Highest-priority strategy: the header equals a target field after lower-casing
 * and trimming (e.g. "Email" -> "email"). Punctuation differences are left to
 * AliasMatch/FuzzyMatch.
 */
final class ExactMatch implements MappingStrategy
{
    public function suggest(string $header, array $targetFields): ?MappingSuggestion
    {
        $needle = strtolower(trim($header));

        foreach ($targetFields as $field) {
            if (strtolower(trim($field)) === $needle) {
                return new MappingSuggestion($field, 1.0, 'exact');
            }
        }

        return null;
    }
}
