<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preserve the historical region relationships required by factories, seeders, and analytics tests.
     *
     * The original migration removed the region scaffolding, which meant our factories could no longer
     * insert related cities during test execution. That cascaded into numerous failing analytics and
     * widget tests. We now invert the behaviour so the migration becomes an idempotent safeguard that
     * reinstates the region table and foreign keys whenever they are missing.
     */
    public function up(): void
    {
        $this->ensureRegionsTable();
        $this->ensureCitiesReferenceRegions();
        $this->ensureAddressesReferenceRegions();
    }

    /**
     * Revert the region scaffolding removal should we ever need to roll back.
     */
    public function down(): void
    {
        $this->dropAddressRegionReference();
        $this->dropCityRegionReference();
        Schema::dropIfExists('regions');
    }

    /**
     * Create the regions table with the original structure when it has been removed.
     */
    private function ensureRegionsTable(): void
    {
        if (Schema::hasTable('regions')) {
            return;
        }

        Schema::create('regions', function (Blueprint $table): void {
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

    /**
     * Ensure the cities table exposes the region reference required by location-aware features.
     */
    private function ensureCitiesReferenceRegions(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        if (! Schema::hasColumn('cities', 'region_id')) {
            Schema::table('cities', function (Blueprint $table): void {
                $table->foreignId('region_id')->nullable()->after('zone_id')->constrained('regions')->onDelete('set null');
            });

            $this->createCityRegionIndex();

            return;
        }

        $this->createCityRegionIndex();
    }

    /**
     * Ensure addresses retain the optional relation to regions for reporting scopes.
     */
    private function ensureAddressesReferenceRegions(): void
    {
        if (! Schema::hasTable('addresses') || Schema::hasColumn('addresses', 'region_id')) {
            return;
        }

        $hasZoneColumn = Schema::hasColumn('addresses', 'zone_id');

        Schema::table('addresses', function (Blueprint $table) use ($hasZoneColumn): void {
            $column = $table->foreignId('region_id')->nullable();

            if ($hasZoneColumn) {
                $column->after('zone_id');
            }

            $column->constrained('regions')->onDelete('set null');
        });
    }

    /**
     * Drop the region reference from cities while tolerating platforms without foreign key helpers.
     */
    private function dropCityRegionReference(): void
    {
        if (! Schema::hasTable('cities') || ! Schema::hasColumn('cities', 'region_id')) {
            return;
        }

        Schema::table('cities', function (Blueprint $table): void {
            try {
                $table->dropIndex('cities_region_id_is_enabled_index');
            } catch (\Throwable $exception) {
                // The index might not exist on legacy installations.
            }

            try {
                $table->dropForeign(['region_id']);
            } catch (\Throwable $exception) {
                // SQLite and some legacy schemas may not expose the foreign key helper.
            }

            $table->dropColumn('region_id');
        });
    }

    /**
     * Drop the region relation from addresses to mirror the previous behaviour.
     */
    private function dropAddressRegionReference(): void
    {
        if (! Schema::hasTable('addresses') || ! Schema::hasColumn('addresses', 'region_id')) {
            return;
        }

        Schema::table('addresses', function (Blueprint $table): void {
            try {
                $table->dropForeign(['region_id']);
            } catch (\Throwable $exception) {
                // SQLite fallbacks do not expose foreign key helpers for dropped relations.
            }

            $table->dropColumn('region_id');
        });
    }

    /**
     * Create the composite city index when it is missing.
     */
    private function createCityRegionIndex(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        try {
            Schema::table('cities', function (Blueprint $table): void {
                $table->index(['region_id', 'is_enabled'], 'cities_region_id_is_enabled_index');
            });
        } catch (\Throwable $exception) {
            // Index already exists or cannot be created on the current platform.
        }
    }
};
