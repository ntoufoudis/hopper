<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\ResolutionType;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Models\FailedRow;
use Ntoufoudis\Hopper\Models\ImportRun;
use Ntoufoudis\Hopper\Models\StagingRow;
use Ntoufoudis\Hopper\Staging\ImportPreview;

function previewRun(): ImportRun
{
    return ImportRun::create([
        'status' => RunStatus::Ready,
        'import_definition' => 'X',
        'source_fingerprint' => 'fp',
    ]);
}

function stage(ImportRun $run, int $number, ResolutionType $type): void
{
    StagingRow::create([
        'run_id' => $run->id,
        'source_row_number' => $number,
        'row_hash' => hash('sha256', "fp:s:$number"),
        'payload' => ['name' => "Row $number"],
        'resolution' => $type,
    ]);
}

it('counts staged verdicts and failed rows without touching the target', function () {
    $run = previewRun();

    stage($run, 1, ResolutionType::Insert);
    stage($run, 2, ResolutionType::Insert);
    stage($run, 3, ResolutionType::Update);
    stage($run, 4, ResolutionType::Skip);

    FailedRow::create([
        'run_id' => $run->id,
        'source_row_number' => 5,
        'row_hash' => hash('sha256', 'fp:f:5'),
        'payload' => ['name' => 'Bad'],
        'reason' => 'invalid',
    ]);

    $preview = $run->preview();

    expect($preview)->toBeInstanceOf(ImportPreview::class)
        ->and($preview->inserts)->toBe(2)
        ->and($preview->updates)->toBe(1)
        ->and($preview->skips)->toBe(1)
        ->and($preview->valid)->toBe(4)
        ->and($preview->errors)->toBe(1)
        ->and($preview->total)->toBe(4);
});
