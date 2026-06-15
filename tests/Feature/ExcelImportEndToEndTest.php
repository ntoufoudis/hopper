<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\ExcelSource;
use Ntoufoudis\Hopper\Tests\Fixtures\Customer;
use Ntoufoudis\Hopper\Tests\Fixtures\UpsertCustomerImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

it('imports an xlsx end to end through the full pipeline', function () {
    $path = tempnam(sys_get_temp_dir(), 'hopper_').'.xlsx';
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        ['name', 'email'],
        ['Alice', 'alice@example.com'],
        ['Bob', 'bob@example.com'],
        ['Carol', 'carol@example.com'],
    ]);
    (new XlsxWriter($spreadsheet))->save($path);

    // Pre-seed one row so the run produces 1 update + 2 inserts.
    Customer::create(['name' => 'Old Bob', 'email' => 'bob@example.com']);

    $run = Hopper::define(UpsertCustomerImport::class)
        ->from(ExcelSource::make($path))
        ->stage();

    $preview = $run->preview();
    $run->commit()->refresh();

    expect($run->status)->toBe(RunStatus::Completed)
        ->and($run->inserted)->toBe(2)
        ->and($run->updated)->toBe(1)
        ->and($preview->inserts)->toBe($run->inserted)
        ->and($preview->updates)->toBe($run->updated)
        ->and(Customer::count())->toBe(3)
        ->and(Customer::where('email', 'carol@example.com')->exists())->toBeTrue();

    unlink($path);
});
