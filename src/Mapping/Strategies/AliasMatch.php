<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping\Strategies;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;
use Ntoufoudis\Hopper\Mapping\MappingSuggestion;

/**
 * Config-driven synonym dictionary: each target field lists known header
 * spellings (e.g. email => ["e-mail", "email address"]). A field is only
 * suggested when it is among the import's target fields.
 */
final readonly class AliasMatch implements MappingStrategy
{
    /**
     * @param  array<string, list<string>>  $aliases
     */
    public function __construct(
        protected array $aliases,
    ) {
        //
    }

    public function suggest(string $header, array $targetFields): ?MappingSuggestion
    {
        $needle = strtolower(trim($header));

        foreach ($this->aliases as $field => $aliasList) {
            if (! in_array($field, $targetFields, true)) {
                continue;
            }

            foreach ($aliasList as $alias) {
                if (strtolower(trim($alias)) === $needle) {
                    return new MappingSuggestion($field, 0.9, 'alias');
                }
            }
        }

        return null;
    }
}
