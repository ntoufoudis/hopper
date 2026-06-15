<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Resolution;

use Illuminate\Database\Eloquent\Model;

/**
 * Matches by a single field; an existing match becomes an Update that
 * overwrites the record with the incoming row's values (provided fields win,
 * including blanks). No match becomes an Insert.
 */
final class UpsertResolver extends DatabaseResolver
{
    /**
     * @param  array<string, mixed>  $row
     */
    protected function applyUpdate(Model $existing, array $row): Model
    {
        $existing->fill($row);

        return $existing;
    }
}
