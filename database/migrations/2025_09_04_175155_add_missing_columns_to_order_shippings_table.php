<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            if (!Schema::hasColumn('order_shippings', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('order_shippings', 'carrier')) {
                $table->string('carrier')->nullable()->after('shipping_method');
            }
            if (!Schema::hasColumn('order_shippings', 'service')) {
                $table->string('service')->nullable()->after('carrier_name');
            }
            if (!Schema::hasColumn('order_shippings', 'service_type')) {
                $table->string('service_type')->nullable()->after('service');
            }
            if (!Schema::hasColumn('order_shippings', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('tracking_url');
            }
            if (!Schema::hasColumn('order_shippings', 'estimated_delivery')) {
                $table->timestamp('estimated_delivery')->nullable()->after('shipped_at');
            }
            if (!Schema::hasColumn('order_shippings', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('estimated_delivery');
            }
            if (!Schema::hasColumn('order_shippings', 'weight')) {
                $table->decimal('weight', 8, 3)->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('order_shippings', 'dimensions')) {
                $table->json('dimensions')->nullable()->after('weight');
            }
            if (!Schema::hasColumn('order_shippings', 'base_cost')) {
                $table->decimal('base_cost', 10, 2)->nullable()->after('dimensions');
            }
            if (!Schema::hasColumn('order_shippings', 'insurance_cost')) {
                $table->decimal('insurance_cost', 10, 2)->nullable()->after('base_cost');
            }
            if (!Schema::hasColumn('order_shippings', 'total_cost')) {
                $table->decimal('total_cost', 10, 2)->nullable()->after('insurance_cost');
            }
            if (!Schema::hasColumn('order_shippings', 'metadata')) {
                $table->json('metadata')->nullable()->after('total_cost');
            }
            if (!Schema::hasColumn('order_shippings', 'status')) {
                $table->string('status')->default('pending')->after('metadata');
            }
            if (!Schema::hasColumn('order_shippings', 'is_delivered')) {
                $table->boolean('is_delivered')->default(false)->after('status');
            }
            if (!Schema::hasColumn('order_shippings', 'delivery_notes')) {
                $table->string('delivery_notes', 500)->nullable()->after('is_delivered');
            }
            if (!Schema::hasColumn('order_shippings', 'notes')) {
                $table->text('notes')->nullable()->after('delivery_notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            $columnsToRemove = [
                'notes',
                'delivery_notes',
                'is_delivered',
                'status',
                'metadata',
                'total_cost',
                'insurance_cost',
                'base_cost',
                'dimensions',
                'weight',
                'delivered_at',
                'estimated_delivery',
                'shipped_at',
                'service_type',
                'service',
                'carrier',
                'shipping_method',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('order_shippings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
