<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:import')]
final class MakeImportCommand extends GeneratorCommand
{
    protected $name = 'make:import';

    protected $description = 'Create a new Hopper import definition';

    protected $type = 'Import';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/import.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Hopper';
    }
}
