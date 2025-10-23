<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table): void {
                $table->id();

                // Core identity fields that power the admin UI and storefront routing.
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code')->unique();
                $table->string('type', 32)->default('web');

                // Optional merchandising and routing metadata.
                $table->text('description')->nullable();
                $table->string('url')->nullable();
                $table->string('domain')->nullable();
                $table->string('timezone')->default('UTC');

                // Currency presentation preferences for storefront rendering.
                $table->char('currency_code', 3)->default('EUR');
                $table->string('currency_symbol', 8)->default('€');
                $table->enum('currency_position', ['before', 'after'])->default('after');

                // Operational toggles surfaced in Filament tables and filters.
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('ssl_enabled')->default(false);
                $table->boolean('analytics_enabled')->default(false);
                $table->unsignedInteger('sort_order')->default(0);

                // Flexible configuration payloads.
                $table->json('metadata')->nullable();
                $table->json('configuration')->nullable();

                $table->timestamps();
                $table->softDeletes();

                // Ensure common lookups remain efficient for reporting and filters.
                $table->index(['is_enabled', 'is_default']);
                $table->index(['type', 'is_active']);
                $table->index(['sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
