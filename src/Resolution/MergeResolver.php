<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Illuminate\Database\Eloquent\Model;

/**
 * Field-level merge of existing + incoming: an incoming value wins only when it
 * is non-blank, so incoming blanks never wipe existing data. No match inserts.
 */
final class MergeResolver extends DatabaseResolver
{
    /**
     * @param  array<string, mixed>  $row
     */
    protected function applyUpdate(Model $existing, array $row): Model
    {
        $merged = [];

        foreach ($row as $field => $value) {
            if ($value !== null && $value !== '') {
                $merged[$field] = $value;
            }
        }

        $existing->fill($merged);

        return $existing;
    }
}
