<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_products')) {
            Schema::table('sh_products', function (Blueprint $table): void {
                if (!Schema::hasColumn('sh_products', 'warehouse_quantity')) {
                    $table->integer('warehouse_quantity')->nullable()->after('security_stock');
                    $table->index('warehouse_quantity', 'sh_products_warehouse_qty_idx');
                }
            });
        }

        // Backfill from variant inventories if table exists
        if (Schema::hasTable('products') && Schema::hasTable('variant_inventories') && Schema::hasTable('product_variants')) {
            $rows = DB::table('products as p')
                ->leftJoin('product_variants as v', 'v.product_id', '=', 'p.id')
                ->leftJoin('variant_inventories as vi', 'vi.variant_id', '=', 'v.id')
                ->select('p.id', DB::raw('COALESCE(SUM(CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END), 0) as qty'))
                ->groupBy('p.id')
                ->get();

            foreach ($rows as $row) {
                DB::table('sh_products')
                    ->where('id', $row->id)
                    ->update(['warehouse_quantity' => (int) $row->qty]);
            }

            // Also handle simple products mistakenly stored directly in inventories
            $rows2 = DB::table('sh_products as p')
                ->leftJoin('sh_variant_inventories as vi', 'vi.variant_id', '=', 'p.id')
                ->select('p.id', DB::raw('COALESCE(SUM(CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END), 0) as qty'))
                ->groupBy('p.id')
                ->get();
            foreach ($rows2 as $row) {
                if ((int) $row->qty > 0) {
                    DB::table('sh_products')
                        ->where('id', $row->id)
                        ->update(['warehouse_quantity' => (int) $row->qty]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sh_products')) {
            Schema::table('sh_products', function (Blueprint $table): void {
                if (Schema::hasColumn('sh_products', 'warehouse_quantity')) {
                    $table->dropIndex('sh_products_warehouse_qty_idx');
                    $table->dropColumn('warehouse_quantity');
                }
            });
        }
    }
};
