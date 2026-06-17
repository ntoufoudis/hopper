<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property string $source_signature
 * @property string $import_definition
 * @property array<string, string> $column_map
 */
final class MappingTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'source_signature',
        'import_definition',
        'column_map',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'column_map' => 'array',
        ];
    }

    public function getTable(): string
    {
        return Config::string('hopper.tables.mapping_templates');
    }
}
