<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('page_route');
            $table->decimal('ttfb_p50', 8, 3)->nullable(); // Time to first byte P50 in milliseconds
            $table->decimal('ttfb_p95', 8, 3)->nullable(); // Time to first byte P95 in milliseconds
            $table->integer('query_count')->default(0);
            $table->integer('peak_memory_mb')->default(0);
            $table->string('environment')->default('local');
            $table->json('additional_metrics')->nullable(); // For extensibility
            $table->timestamps();

            $table->index(['page_route', 'created_at']);
            $table->index('environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
