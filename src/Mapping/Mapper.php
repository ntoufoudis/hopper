<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Mapping;

use Ntoufoudis\Hopper\Contracts\MappingStrategy;
use Ntoufoudis\Hopper\Models\MappingTemplate;

final readonly class Mapper
{
    /**
     * @param  list<MappingStrategy>  $strategies
     */
    public function __construct(
        protected array $strategies,
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

    /**
     * Template-first resolution: reuse a saved map for this (signature,
     * definition) pair; otherwise run strategies and persist the result so the
     * next import of the same source layout is zero-touch.
     *
     * @param  list<string>  $headers
     * @param  list<string>  $targetFields
     */
    public function autoMap(string $signature, string $definition, array $headers, array $targetFields): ColumnMap
    {
        $template = MappingTemplate::query()
            ->where('source_signature', $signature)
            ->where('import_definition', $definition)
            ->first();

        if ($template !== null) {
            return new ColumnMap($template->column_map);
        }

        $map = $this->strategyMap($headers, $targetFields);

        // Only persist a usable template; saving an empty/no-match map would
        // poison the (signature, definition) pair, short-circuiting every later
        // import of this layout to the bad template before strategies re-run.
        if ($map->toArray() !== []) {
            $this->saveTemplate($signature, $definition, $map);
        }

        return $map;
    }

    public function saveTemplate(string $signature, string $definition, ColumnMap $map): MappingTemplate
    {
        return MappingTemplate::query()->updateOrCreate(
            ['source_signature' => $signature, 'import_definition' => $definition],
            ['column_map' => $map->toArray()],
        );
    }
}
