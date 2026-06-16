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

    /** @var array<string, Model> existing records keyed by case-folded match value (unambiguous only) */
    protected array $existingFolded = [];

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
        $this->existingFolded = [];

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

        $foldCounts = [];

        foreach ($found as $model) {
            $value = $model->getAttribute($this->field);

            if (! is_scalar($value)) {
                continue;
            }

            $key = (string) $value;
            $this->existing[$key] = $model;

            $folded = mb_strtolower($key);
            $foldCounts[$folded] = ($foldCounts[$folded] ?? 0) + 1;
            $this->existingFolded[$folded] = $model;
        }

        // Drop folded keys that collapse two distinct stored values, so
        // case-sensitive data is never mis-matched by the fallback.
        foreach ($foldCounts as $folded => $count) {
            if ($count > 1) {
                unset($this->existingFolded[$folded]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function resolve(array $row): Resolution
    {
        $value = $row[$this->field] ?? null;

        if (! is_scalar($value)) {
            return new Resolution(ResolutionType::Insert);
        }

        $key = (string) $value;
        $match = $this->existing[$key]
            ?? $this->existingFolded[mb_strtolower($key)]
            ?? null;

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
