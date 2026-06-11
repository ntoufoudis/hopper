<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\Enums\ResolutionType;

final class InsertOnlyResolver implements Resolver
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function resolve(array $row): Resolution
    {
        return new Resolution(ResolutionType::Insert);
    }
}
