<?php

declare(strict_types=1);

use App\Hopper\GeneratedCustomerImport;
use Illuminate\Support\Facades\File;
use Ntoufoudis\Hopper\Enums\RunStatus;
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;

afterEach(function () {
    File::delete(app_path('Hopper/ProductImport.php'));
    File::delete(app_path('Hopper/GeneratedCustomerImport.php'));
});

it('generates an ImportDefinition stub with the expected methods', function () {
    $this->artisan('make:import', ['name' => 'ProductImport'])->assertExitCode(0);

    $path = app_path('Hopper/ProductImport.php');
    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain('namespace App\\Hopper;')
        ->and($contents)->toContain('class ProductImport extends ImportDefinition')
        ->and($contents)->toContain('public function model(): string')
        ->and($contents)->toContain('public function rules(): array')
        ->and($contents)->toContain('public function pipes(): array')
        ->and($contents)->toContain('public function resolver(): Resolver');
});

it('generates a definition that stages successfully', function () {
    $this->artisan('make:import', ['name' => 'GeneratedCustomerImport'])->assertExitCode(0);

    // Point the generated stub at the test Customer model, then load it.
    $path = app_path('Hopper/GeneratedCustomerImport.php');
    File::put($path, str_replace(
        '\\App\\Models\\Model::class',
        '\\Ntoufoudis\\Hopper\\Tests\\Fixtures\\Customer::class',
        File::get($path),
    ));
    require $path;

    $run = Hopper::define(GeneratedCustomerImport::class)
        ->from(CsvSource::make(__DIR__.'/../Fixtures/customers.csv'))
        ->stage();

    expect($run->status)->toBe(RunStatus::Ready)
        ->and($run->total)->toBe(5);
});
