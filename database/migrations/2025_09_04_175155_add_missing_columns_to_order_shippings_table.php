<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_shippings', 'service')) {
                $table->string('service')->nullable()->after('carrier_name');
            }
            if (! Schema::hasColumn('order_shippings', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('tracking_url');
            }
            if (! Schema::hasColumn('order_shippings', 'estimated_delivery')) {
                $table->timestamp('estimated_delivery')->nullable()->after('shipped_at');
            }
            if (! Schema::hasColumn('order_shippings', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('estimated_delivery');
            }
            if (! Schema::hasColumn('order_shippings', 'weight')) {
                $table->decimal('weight', 8, 3)->nullable()->after('delivered_at');
            }
            if (! Schema::hasColumn('order_shippings', 'dimensions')) {
                $table->json('dimensions')->nullable()->after('weight');
            }
            if (! Schema::hasColumn('order_shippings', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('dimensions');
            }
            if (! Schema::hasColumn('order_shippings', 'metadata')) {
                $table->json('metadata')->nullable()->after('cost');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            $columnsToRemove = [
                'metadata',
                'cost',
                'dimensions',
                'weight',
                'delivered_at',
                'estimated_delivery',
                'shipped_at',
                'service',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('order_shippings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
