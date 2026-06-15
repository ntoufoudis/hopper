<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Illuminate\Database\Eloquent\Model;
use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Enums\ResolutionType;

/**
 * Base for resolvers that match incoming rows against existing target records
 * by a single field. Batches the lookup: prime() runs one keyed whereIn per
 * chunk and caches the matches in memory, so resolve() never queries per row.
 */
abstract class DatabaseResolver implements Resolver
{
    /** @var class-string<Model>|null */
    protected ?string $modelClass = null;

    /** @var array<string, Model> existing records keyed by match-field value */
    protected array $existing = [];

    final public function __construct(
        protected readonly string $field,
    ) {
        //
    }

    public static function by(string $field): static
    {
        return new static($field);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function useModel(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Load every existing target record matching any of the chunk's field
     * values in a single query, keyed by the field value for in-memory lookup.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function prime(array $rows): void
    {
        $this->existing = [];

        $modelClass = $this->modelClass;

        if ($modelClass === null) {
            return;
        }

        $values = [];

        foreach ($rows as $row) {
            $value = $row[$this->field] ?? null;

            if (is_scalar($value) && $value !== '') {
                $values[(string) $value] = true;
            }
        }

        if ($values === []) {
            return;
        }

        $found = $modelClass::query()->whereIn($this->field, array_keys($values))->get();

        foreach ($found as $model) {
            $value = $model->getAttribute($this->field);

            if (is_scalar($value)) {
                $this->existing[(string) $value] = $model;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolve(array $row): Resolution
    {
        $value = $row[$this->field] ?? null;
        $match = (is_scalar($value) && isset($this->existing[(string) $value]))
            ? $this->existing[(string) $value]
            : null;

        if ($match === null) {
            return new Resolution(ResolutionType::Insert);
        }

        return new Resolution(ResolutionType::Update, model: $this->applyUpdate($match, $row));
    }

    /**
     * Apply the incoming row onto the matched model, returning the model whose
     * fillable attributes are the values to persist on update.
     *
     * @param  array<string, mixed>  $row
     */
    abstract protected function applyUpdate(Model $existing, array $row): Model;
}
