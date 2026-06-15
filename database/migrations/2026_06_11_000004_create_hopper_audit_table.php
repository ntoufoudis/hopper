<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('hopper.tables.audit'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('run_id')->nullable()->index();
            $table->string('event');
            $table->json('context');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('hopper.tables.audit'));
    }
};
