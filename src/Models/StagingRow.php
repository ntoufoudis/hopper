<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Ntoufoudis\Hopper\Enums\ResolutionType;

/**
 * @property int $source_row_number
 * @property string $row_hash
 * @property array<string, mixed> $payload
 * @property ResolutionType $resolution
 * @property ?string $resolved_key
 * @property ?Carbon $committed_at
 */
final class StagingRow extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'run_id',
        'source_row_number',
        'row_hash',
        'payload',
        'resolution',
        'resolved_key',
        'committed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return string[]
     */
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
