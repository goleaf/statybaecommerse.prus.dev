<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_orders')) {
            return;
        }
        Schema::table('sh_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('sh_orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 2)->default(0)->after('notes');
            }
            if (!Schema::hasColumn('sh_orders', 'discount_total_amount')) {
                $table->decimal('discount_total_amount', 12, 2)->default(0)->after('subtotal_amount');
            }
            if (!Schema::hasColumn('sh_orders', 'tax_total_amount')) {
                $table->decimal('tax_total_amount', 12, 2)->default(0)->after('discount_total_amount');
            }
            if (!Schema::hasColumn('sh_orders', 'shipping_total_amount')) {
                $table->decimal('shipping_total_amount', 12, 2)->default(0)->after('tax_total_amount');
            }
            if (!Schema::hasColumn('sh_orders', 'grand_total_amount')) {
                $table->decimal('grand_total_amount', 12, 2)->default(0)->after('shipping_total_amount');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sh_orders')) {
            return;
        }
        Schema::table('sh_orders', function (Blueprint $table): void {
            foreach ([
                'grand_total_amount',
                'shipping_total_amount',
                'tax_total_amount',
                'discount_total_amount',
                'subtotal_amount',
            ] as $col) {
                if (Schema::hasColumn('sh_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
