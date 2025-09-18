<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (! Schema::hasColumn('zones', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }

                if (! Schema::hasColumn('zones', 'code')) {
                    $table->string('code', 10)->nullable()->unique()->after('slug');
                }

                if (! Schema::hasColumn('zones', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }

                if (! Schema::hasColumn('zones', 'currency_id') && Schema::hasTable('currencies')) {
                    $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
                } elseif (! Schema::hasColumn('zones', 'currency_id')) {
                    $table->unsignedBigInteger('currency_id')->nullable();
                }

                if (Schema::hasColumn('zones', 'tax_rate')) {
                    try {
                        DB::statement('ALTER TABLE `zones` MODIFY `tax_rate` DECIMAL(8,4) DEFAULT 0.0');
                    } catch (\Throwable $e) {
                        // Ignore if modification fails; requires manual adjustment
                    }
                } else {
                    $table->decimal('tax_rate', 8, 4)->default(0.0);
                }

                if (! Schema::hasColumn('zones', 'shipping_rate')) {
                    $table->decimal('shipping_rate', 10, 2)->default(0.0);
                }

                if (! Schema::hasColumn('zones', 'metadata')) {
                    $table->json('metadata')->nullable();
                }

                if (! Schema::hasColumn('zones', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }

                if (! Schema::hasColumn('zones', 'created_at')) {
                    $table->timestamps();
                }

                if (! Schema::hasIndex('zones', 'zones_is_enabled_is_default_index')) {
                    $table->index(['is_enabled', 'is_default']);
                }

                if (! Schema::hasIndex('zones', 'zones_code_is_enabled_index')) {
                    $table->index(['code', 'is_enabled']);
                }
            });

            return;
        }

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            $table->decimal('tax_rate', 8, 4)->default(0.0)->comment('Tax rate as percentage (e.g., 21.0000 for 21%)');
            $table->decimal('shipping_rate', 10, 2)->default(0.0)->comment('Base shipping rate');
            $table->json('metadata')->nullable()->comment('Additional zone configuration');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'is_default']);
            $table->index(['code', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
