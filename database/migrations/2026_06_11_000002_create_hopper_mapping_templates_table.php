<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('hopper.tables.mapping_templates'), function (Blueprint $table): void {
            $table->id();
            $table->string('source_signature');
            $table->string('import_definition');
            $table->json('column_map');
            $table->timestamps();

            $table->unique(['source_signature', 'import_definition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('hopper.tables.mapping_templates'));
    }
};
