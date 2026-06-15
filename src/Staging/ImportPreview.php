<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

/**
 * Pre-commit counts for one run, read entirely from persisted staging verdicts.
 * valid = inserts + updates + skips (rows that staged); errors = diverted/failed
 * rows; total = valid + errors.
 */
final readonly class ImportPreview
{
    public function __construct(
        public int $total,
        public int $valid,
        public int $errors,
        public int $inserts,
        public int $updates,
        public int $skips,
    ) {
        //
    }

    /**
     * @return array{total: int, valid: int, errors: int, inserts: int, updates: int, skips: int}
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'valid' => $this->valid,
            'errors' => $this->errors,
            'inserts' => $this->inserts,
            'updates' => $this->updates,
            'skips' => $this->skips,
        ];
    }
}
