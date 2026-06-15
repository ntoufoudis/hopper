<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Database\Eloquent\Model;
use Ntoufoudis\Hopper\Contracts\Pipe;
use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Resolution\InsertOnlyResolver;

abstract class ImportDefinition
{
    /** @return class-string<Model> */
    abstract public function model(): string;

    /**
     * Per-row validation rules, keyed by target field. Invalid rows are
     * diverted to the failed-row store instead of being staged.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Row transformation pipes, applied (in order) before validation.
     *
     * @return list<class-string<Pipe>|Pipe>
     */
    public function pipes(): array
    {
        return [];
    }

    /**
     * Target fields a source maps onto. Defaults to the model's fillable.
     *
     * @return list<string>
     */
    public function fields(): array
    {
        $model = new ($this->model());

        return array_values($model->getFillable());
    }

    public function resolver(): Resolver
    {
        return new InsertOnlyResolver;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
