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
        Schema::create('import_row_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('status');
            $table->string('action');
            $table->text('message')->nullable();
            $table->text('error_message')->nullable();
            $table->json('changed_fields')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_row_results');
    }
};
