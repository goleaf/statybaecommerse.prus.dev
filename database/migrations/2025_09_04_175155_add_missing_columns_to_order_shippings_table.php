<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_shippings', function (Blueprint $table): void {
            $table->string('service')->nullable()->after('carrier_name');
            $table->timestamp('shipped_at')->nullable()->after('tracking_url');
            $table->timestamp('estimated_delivery')->nullable()->after('shipped_at');
            $table->timestamp('delivered_at')->nullable()->after('estimated_delivery');
            $table->decimal('weight', 8, 3)->nullable()->after('delivered_at');
            $table->json('dimensions')->nullable()->after('weight');
            $table->decimal('cost', 15, 2)->nullable()->after('dimensions');
            $table->json('metadata')->nullable()->after('cost');
        });
    }

    public function down(): void
    {
        Schema::table('order_shippings', function (Blueprint $table): void {
            $table->dropColumn([
                'metadata',
                'cost',
                'dimensions',
                'weight',
                'delivered_at',
                'estimated_delivery',
                'shipped_at',
                'service',
            ]);
        });
    }
};
