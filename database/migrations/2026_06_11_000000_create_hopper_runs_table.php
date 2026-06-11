<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('hopper.tables.runs'), function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('pending');
            $table->string('import_definition');
            $table->string('source_fingerprint')->index();
            $table->nullableMorphs('actor');
            $table->unsignedInteger('total')->nullable();
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('inserted')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('hopper.tables.runs'));
    }
};
