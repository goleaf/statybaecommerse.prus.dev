<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_history_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_history_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5);
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->string('field_name')->nullable();

            $table->unique(['product_history_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_history_translations');
    }
};
