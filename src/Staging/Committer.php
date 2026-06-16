<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Ntoufoudis\Hopper\Audit\ImportEvent;
use Ntoufoudis\Hopper\Contracts\AuditDriver;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Throwable;

final class Committer
{
    /**
     * @throws Throwable
     */
    public function commit(ImportRun $run): void
    {
        if ($run->status !== RunStatus::Importing) {
            $run->update(['status' => RunStatus::Importing]);
        }

        if ($run->started_at === null) {
            $run->update(['started_at' => Date::now()]);
        }

        $audit = app(AuditDriver::class);
        $audit->record(new ImportEvent('commit.started', $run->id));

        try {
            while ($this->commitChunk($run) > 0) {
                // Replay one chunk at a time until no uncommitted rows remain.
            }
        } catch (Throwable $e) {
            // Earlier chunks commit in their own transactions, so a later failure
            // can leave the run partially imported.
            $status = $run->processed > 0
                ? RunStatus::PartiallyCompleted
                : RunStatus::Failed;

            $run->update(['status' => $status]);
            $audit->record(new ImportEvent('commit.failed', $run->id, [
                'error' => $e->getMessage(),
            ]));

            throw $e;
        }

        $run->update([
            'status' => RunStatus::Completed,
            'completed_at' => Date::now(),
        ]);
        $audit->record(new ImportEvent('commit.completed', $run->id, [
            'inserted' => $run->inserted,
            'updated' => $run->updated,
            'skipped' => $run->skipped,
        ]));
    }

    public function commitChunk(ImportRun $run): int
    {
        /** @var ImportDefinition $definition */
        $definition = new ($run->import_definition);
        $modelClass = $definition->model();

        $rows = StagingRow::query()
            ->where('run_id', $run->id)
            ->whereNull('committed_at')
            ->orderBy('id')
            ->limit($definition->chunkSize())
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($definition, $rows, $modelClass, $run): void {
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $allowed = array_flip($definition->fields());

            foreach ($rows as $row) {
                $attributes = array_intersect_key($row->payload, $allowed);

                switch ($row->resolution) {
                    case ResolutionType::Insert:
                        $modelClass::query()->create($attributes);
                        $inserted++;
                        break;
                    case ResolutionType::Update:
                        $modelClass::query()->whereKey($row->resolved_key)->update($attributes);
                        $updated++;
                        break;
                    case ResolutionType::Skip:
                        $skipped++;
                        break;
                }

                $row->update(['committed_at' => now()]);
            }

            $run->increment('inserted', $inserted);
            $run->increment('updated', $updated);
            $run->increment('skipped', $skipped);
            $run->increment('processed', $rows->count());
        });

        return $rows->count();
    }
}
