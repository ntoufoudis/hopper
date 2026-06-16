<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property ?int $run_id
 * @property string $event
 * @property array<string, mixed> $context
 * @property Carbon $occurred_at
 */
#[Fillable([
    'run_id',
    'event',
    'context',
    'occurred_at',
])]
final class AuditEvent extends Model
{
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return Config::string('hopper.tables.audit');
    }
}
