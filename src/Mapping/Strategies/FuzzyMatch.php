<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping\Strategies;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;
use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

/**
 * Lowest-priority strategy: normalises header and field (lowercase, strip all
 * non-alphanumerics) and scores similarity as 1 - levenshtein/maxLength. The
 * best-scoring field at or above the configured threshold wins.
 */
final readonly class FuzzyMatch implements MappingStrategy
{
    public function __construct(
        protected float $threshold = 0.8,
    ) {
        //
    }

    public function suggest(string $header, array $targetFields): ?MappingSuggestion
    {
        $needle = $this->normalise($header);

        if ($needle === '') {
            return null;
        }

        $bestField = null;
        $bestScore = 0.0;

        foreach ($targetFields as $field) {
            $score = $this->similarity($needle, $this->normalise($field));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestField = $field;
            }
        }

        if ($bestField === null || $bestScore < $this->threshold) {
            return null;
        }

        return new MappingSuggestion($bestField, $bestScore, 'fuzzy');
    }

    protected function normalise(string $value): string
    {
        return (string) preg_replace('/[^a-z0-9]+/', '', strtolower($value));
    }

    protected function similarity(string $a, string $b): float
    {
        $max = max(strlen($a), strlen($b));

        if ($max === 0) {
            return 0.0;
        }

        return 1.0 - (levenshtein($a, $b) / $max);
    }
}
