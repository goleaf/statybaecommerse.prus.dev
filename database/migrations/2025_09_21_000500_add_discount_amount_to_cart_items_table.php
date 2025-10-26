<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cart_items')) {
            // Bail out when the table is missing so older deployments do not fail.
            return;
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('cart_items', 'discount_amount')) {
                // Store cart-level discounts alongside unit price for accurate totals.
                $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            if (Schema::hasColumn('cart_items', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
