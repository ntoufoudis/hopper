<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Enums;

enum ResolutionType: string
{
    case Insert = 'insert';
    case Update = 'update';
    case Skip = 'skip';
}
