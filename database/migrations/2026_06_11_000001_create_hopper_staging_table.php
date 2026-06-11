<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('hopper.tables.staging'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('run_id');
            $table->unsignedInteger('source_row_number');
            $table->string('row_hash')->unique();
            $table->json('payload');
            $table->string('resolution');
            $table->string('resolved_key')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();

            $table->index(['run_id', 'committed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('hopper.tables.staging'));
    }
};
