<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('countries') || Schema::hasTable('cities')) {
            return;
        }

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_capital')->default(false);
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('cities')->onDelete('cascade');
            $table->integer('level')->default(0)->comment('Hierarchy level: 0=city, 1=district, 2=neighborhood, etc.');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->bigInteger('population')->nullable();
            $table->json('postal_codes')->nullable()->comment('Array of postal codes for this city');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional city configuration');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_enabled', 'is_default']);
            $table->index(['is_capital', 'is_enabled']);
            $table->index(['code', 'is_enabled']);
            $table->index(['country_id', 'is_enabled']);
            $table->index(['zone_id', 'is_enabled']);
            $table->index(['region_id', 'is_enabled']);
            $table->index(['parent_id', 'level']);
            $table->index(['level', 'sort_order']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
