<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property int $run_id
 * @property int $source_row_number
 * @property string $row_hash
 * @property array<string, mixed> $payload
 * @property string $reason
 */
#[Fillable([
    'run_id',
    'source_row_number',
    'row_hash',
    'payload',
    'reason',
])]
final class FailedRow extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function getTable(): string
    {
        return Config::string('hopper.tables.failed_rows');
    }
}
