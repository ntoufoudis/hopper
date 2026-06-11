<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Illuminate\Database\Eloquent\Model;
use Ntoufoudis\Hopper\Enums\ResolutionType;

final readonly class Resolution
{
    public function __construct(
        public ResolutionType $type,
        public ?Model $model = null,
        public ?string $reason = null,
    ) {
        //
    }
}
