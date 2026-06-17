<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;

/**
 * Builds an ImportPreview from persisted verdicts only: one GROUP BY over
 * hopper_staging for insert/update/skip counts plus a hopper_failed_rows count.
 * Never queries the target model.
 */
final class PreviewBuilder
{
    public function build(ImportRun $run): ImportPreview
    {
        $counts = StagingRow::query()
            ->where('run_id', $run->id)
            ->groupBy('resolution')
            ->selectRaw('resolution, COUNT(*) as aggregate')
            ->pluck('aggregate', 'resolution')
            ->map(static fn (mixed $value): int => is_numeric($value) ? (int) $value : 0)
            ->all();

        $inserts = $counts[ResolutionType::Insert->value] ?? 0;
        $updates = $counts[ResolutionType::Update->value] ?? 0;
        $skips = $counts[ResolutionType::Skip->value] ?? 0;

        $valid = $inserts + $updates + $skips;
        $errors = FailedRow::query()->where('run_id', $run->id)->count();

        $preview = new ImportPreview(
            total: $valid,
            valid: $valid,
            errors: $errors,
            inserts: $inserts,
            updates: $updates,
            skips: $skips,
        );

        app(AuditDriver::class)->record(new ImportEvent('preview.generated', $run->id, $preview->toArray()));

        return $preview;
    }
}
