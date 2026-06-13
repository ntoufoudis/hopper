<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property string $source_signature
 * @property string $import_definition
 * @property array<string, string> $column_map
 */
#[Fillable([
    'source_signature',
    'import_definition',
    'column_map',
])]
final class MappingTemplate extends Model
{
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
