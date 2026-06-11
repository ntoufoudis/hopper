<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Ntoufoudis\Hopper\Enums\ResolutionType;

/**
 * @property array<string, mixed> $payload
 * @property ResolutionType $resolution
 * @property ?string $resolved_key
 * @property ?Carbon $committed_at
 */
#[Fillable([
    'run_id',
    'source_row_number',
    'row_hash',
    'payload',
    'resolution',
    'resolved_key',
    'committed_at',
])]
final class StagingRow extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'resolution' => ResolutionType::class,
            'committed_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return Config::string('hopper.tables.staging');
    }
}
