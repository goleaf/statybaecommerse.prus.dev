<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            // Translatable attributes are stored as JSON to support multilingual names and descriptions.
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('code', 3)->unique();
            $table->string('iso_code', 3)->nullable()->unique();
            $table->string('symbol', 10)->nullable();
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('base_currency', 3)->default('EUR');
            $table->tinyInteger('decimal_places')->default(2);
            $table->string('symbol_position')->default('after');
            $table->string('thousands_separator', 1)->default(',');
            $table->string('decimal_separator', 1)->default('.');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('auto_update_rate')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Optimise lookups for frequently queried flags and codes.
            $table->index(['code']);
            $table->index(['is_active', 'is_enabled']);
            $table->index(['is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
