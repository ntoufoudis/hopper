<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;

final class Mapper
{
    /**
     * @param  list<MappingStrategy>  $strategies
     */
    public function __construct(
        private array $strategies,
    ) {
        //
    }

    /**
     * The winning suggestion per header (headers no strategy can place are
     * omitted). Confidence/strategy are exposed for human review.
     *
     * @param  list<string>  $headers
     * @param  list<string>  $targetFields
     * @return array<string, MappingSuggestion>
     */
    public function suggestions(array $headers, array $targetFields): array
    {
        $suggestions = [];

        foreach ($headers as $header) {
            foreach ($this->strategies as $strategy) {
                $suggestion = $strategy->suggest($header, $targetFields);

                if ($suggestion !== null) {
                    $suggestions[$header] = $suggestion;
                    break;
                }
            }
        }

        return $suggestions;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $targetFields
     */
    public function strategyMap(array $headers, array $targetFields): ColumnMap
    {
        $map = [];

        foreach ($this->suggestions($headers, $targetFields) as $header => $suggestion) {
            $map[$header] = $suggestion->field;
        }

        return new ColumnMap($map);
    }
}
