<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce country and city references ahead of the zone removal process.
     */
    public function up(): void
    {
        // Extend the shipping options table with country and city lookups so UI flows stay operational.
        if (Schema::hasTable('shipping_options')) {
            Schema::table('shipping_options', function (Blueprint $table): void {
                if (! Schema::hasColumn('shipping_options', 'country_id')) {
                    // Nullable during the transition – enforced later once data is backfilled.
                    $table->foreignId('country_id')->nullable()->after('currency_code')->constrained('countries')->nullOnDelete();
                }

                if (! Schema::hasColumn('shipping_options', 'city_id')) {
                    // City remains optional because some shipping methods might stay country-wide.
                    $table->foreignId('city_id')->nullable()->after('country_id')->constrained('cities')->nullOnDelete();
                }

                try {
                    // Maintain performant lookups when filtering by country and city in Filament tables.
                    $table->index(['country_id', 'city_id'], 'shipping_options_country_city_idx');
                } catch (\Throwable $exception) {
                    // Ignore attempts to recreate the index when it already exists.
                }
            });
        }

        // Attach a country fallback to orders so reporting scopes can pivot away from the zone reference.
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'country_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('channel_id')->constrained('countries')->nullOnDelete();
            });
        }

        // Allow price lists to target countries directly instead of the now deprecated zone relation.
        if (Schema::hasTable('price_lists') && ! Schema::hasColumn('price_lists', 'country_id')) {
            Schema::table('price_lists', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('currency_id')->constrained('countries')->nullOnDelete();

                try {
                    $table->index(['country_id', 'is_enabled'], 'price_lists_country_enabled_idx');
                } catch (\Throwable $exception) {
                    // The index might already exist on fresh installations – ignore duplicates gracefully.
                }
            });
        }

        // Discounts and campaigns gain country anchors to replace zone segmentation filters.
        if (Schema::hasTable('discounts') && ! Schema::hasColumn('discounts', 'country_id')) {
            Schema::table('discounts', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('zone_id')->constrained('countries')->nullOnDelete();
            });
        }

        if (Schema::hasTable('discount_campaigns') && ! Schema::hasColumn('discount_campaigns', 'country_id')) {
            Schema::table('discount_campaigns', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('channel_id')->constrained('countries')->nullOnDelete();

                try {
                    $table->index(['country_id', 'status'], 'discount_campaigns_country_status_idx');
                } catch (\Throwable $exception) {
                    // Duplicate index creation attempts are safe to ignore.
                }
            });
        }

        // Regions already reference countries but we keep the issue tracker for unmapped historical rows.
        if (! Schema::hasTable('zone_migration_issues')) {
            Schema::create('zone_migration_issues', function (Blueprint $table): void {
                $table->id();
                $table->string('table_name');
                $table->unsignedBigInteger('record_id');
                $table->string('zone_code')->nullable();
                $table->string('note')->nullable();
                $table->timestamps();

                $table->index(['table_name', 'record_id'], 'zone_migration_lookup_idx');
            });
        }
    }

    /**
     * Roll back the preparatory columns in case the migration needs to be reverted.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipping_options')) {
            Schema::table('shipping_options', function (Blueprint $table): void {
                if (Schema::hasColumn('shipping_options', 'country_id')) {
                    $table->dropConstrainedForeignId('country_id');
                }

                if (Schema::hasColumn('shipping_options', 'city_id')) {
                    $table->dropConstrainedForeignId('city_id');
                }

                try {
                    $table->dropIndex('shipping_options_country_city_idx');
                } catch (\Throwable $exception) {
                    // Index may be missing on SQLite or older setups; ignore gracefully.
                }
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'country_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('country_id');
            });
        }

        if (Schema::hasTable('price_lists') && Schema::hasColumn('price_lists', 'country_id')) {
            Schema::table('price_lists', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('country_id');

                try {
                    $table->dropIndex('price_lists_country_enabled_idx');
                } catch (\Throwable $exception) {
                    // Ignore index drop failures during rollback.
                }
            });
        }

        if (Schema::hasTable('discounts') && Schema::hasColumn('discounts', 'country_id')) {
            Schema::table('discounts', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('country_id');
            });
        }

        if (Schema::hasTable('discount_campaigns') && Schema::hasColumn('discount_campaigns', 'country_id')) {
            Schema::table('discount_campaigns', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('country_id');

                try {
                    $table->dropIndex('discount_campaigns_country_status_idx');
                } catch (\Throwable $exception) {
                    // The index might not exist if the migration partially ran; continue silently.
                }
            });
        }

        Schema::dropIfExists('zone_migration_issues');
    }
};
