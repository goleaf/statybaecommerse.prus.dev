<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Augment the coupons table with targeting columns expected by the Filament resource and feature tests.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            if (! Schema::hasColumn('coupons', 'is_first_time_only')) {
                // Track whether a coupon applies exclusively to first-time shoppers.
                $table->boolean('is_first_time_only')->default(false)->after('is_stackable');
            }

            if (! Schema::hasColumn('coupons', 'customer_group_id')) {
                // Reference the optional customer group segmentation without breaking existing rows.
                $table->foreignId('customer_group_id')->nullable()->after('is_first_time_only')->constrained('customer_groups')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            if (Schema::hasColumn('coupons', 'customer_group_id')) {
                // Drop the foreign key before removing the column for portability across drivers.
                $table->dropConstrainedForeignId('customer_group_id');
            }

            if (Schema::hasColumn('coupons', 'is_first_time_only')) {
                $table->dropColumn('is_first_time_only');
            }
        });
    }
};
