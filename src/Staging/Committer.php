<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Staging;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;

final class Committer
{
    public function commit(ImportRun $run): void
    {
        if ($run->status !== RunStatus::Importing) {
            $run->update(['status' => RunStatus::Importing]);
        }

        while ($this->commitChunk($run) > 0) {
            // Replay one chunk at a time until no uncommitted rows remain.
        }

        $run->update(['status' => RunStatus::Completed]);
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

        DB::transaction(function () use ($rows, $modelClass, $run): void {
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            /** @var Model $prototype */
            $prototype = new $modelClass;
            $fillable = $prototype->getFillable();

            foreach ($rows as $row) {
                $attributes = $fillable === []
                    ? $row->payload
                    : array_intersect_key($row->payload, array_flip($fillable));

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
