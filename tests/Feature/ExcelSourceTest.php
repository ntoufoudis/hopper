<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Sources\ExcelSource;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

function makeXlsxFixture(): string
{
    $path = tempnam(sys_get_temp_dir(), 'hopper_').'.xlsx';

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        ['name', 'email'],
        ['Alice', 'alice@example.com'],
        ['Bob', 'bob@example.com'],
        ['Carol', 'carol@example.com'],
    ]);

    (new XlsxWriter($spreadsheet))->save($path);

    return $path;
}

it('reads headers, streams header-keyed rows, and fingerprints an xlsx', function () {
    $path = makeXlsxFixture();

    $source = ExcelSource::make($path);

    expect($source->headers())->toBe(['name', 'email']);

    $rows = iterator_to_array($source->rows());

    expect($rows)->toHaveCount(3)
        ->and($rows[1])->toBe(['name' => 'Alice', 'email' => 'alice@example.com'])
        ->and($rows[3])->toBe(['name' => 'Carol', 'email' => 'carol@example.com'])
        ->and($source->fingerprint())->toBe(hash('sha256', $path.':'.hash_file('sha256', $path)));

    unlink($path);
});
