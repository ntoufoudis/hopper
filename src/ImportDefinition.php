<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper;

use Illuminate\Database\Eloquent\Model;
use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Resolution\InsertOnlyResolver;

abstract class ImportDefinition
{
    /** @return class-string<Model> */
    abstract public function model(): string;

    public function resolver(): Resolver
    {
        return new InsertOnlyResolver;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
