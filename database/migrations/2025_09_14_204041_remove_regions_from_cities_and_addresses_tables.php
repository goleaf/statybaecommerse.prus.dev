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
        // Remove region_id from cities table
        if (Schema::hasTable('cities') && Schema::hasColumn('cities', 'region_id')) {
            try {
                DB::statement('ALTER TABLE `cities` DROP FOREIGN KEY `cities_region_id_foreign`');
            } catch (\Throwable $e) {
                // Foreign key might not exist
            }

            try {
                DB::statement('ALTER TABLE `cities` DROP INDEX `cities_region_id_is_enabled_index`');
            } catch (\Throwable $e) {
                // Index might not exist
            }

            Schema::table('cities', function (Blueprint $table) {
                if (Schema::hasColumn('cities', 'region_id')) {
                    $table->dropColumn('region_id');
                }
            });
        }

        // Remove region_id from addresses table
        if (Schema::hasTable('addresses') && Schema::hasColumn('addresses', 'region_id')) {
            try {
                DB::statement('ALTER TABLE `addresses` DROP FOREIGN KEY `addresses_region_id_foreign`');
            } catch (\Throwable $e) {
                // Foreign key might not exist
            }

            Schema::table('addresses', function (Blueprint $table) {
                if (Schema::hasColumn('addresses', 'region_id')) {
                    $table->dropColumn('region_id');
                }
            });
        }

        // Drop the regions table
        Schema::dropIfExists('regions');
    }

    public function down(): void
    {
        // Recreate regions table if needed
        if (! Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('regions')->onDelete('cascade');
            $table->integer('level')->default(0)->comment('Hierarchy level: 0=root, 1=state/province, 2=county, etc.');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional region configuration');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_enabled', 'is_default']);
            $table->index(['code', 'is_enabled']);
            $table->index(['country_id', 'is_enabled']);
            $table->index(['zone_id', 'is_enabled']);
            $table->index(['parent_id', 'level']);
            $table->index(['level', 'sort_order']);
            });
        }

        // Add region_id back to cities table
        if (Schema::hasTable('cities') && ! Schema::hasColumn('cities', 'region_id')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->foreignId('region_id')->nullable()->after('zone_id')->constrained('regions')->onDelete('set null');
                $table->index(['region_id', 'is_enabled']);
            });
        }

        // Add region_id back to addresses table
        if (Schema::hasTable('addresses') && ! Schema::hasColumn('addresses', 'region_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->foreignId('region_id')->nullable()->after('zone_id')->constrained('regions')->onDelete('set null');
            });
        }
    }
};
