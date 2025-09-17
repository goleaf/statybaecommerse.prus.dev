<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sh_order_shippings')) {
            return;
        }
        Schema::table('sh_order_shippings', function (Blueprint $table): void {
            if (! Schema::hasColumn('sh_order_shippings', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('carrier_name');
            }
            if (! Schema::hasColumn('sh_order_shippings', 'tracking_url')) {
                $table->string('tracking_url')->nullable()->after('tracking_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sh_order_shippings')) {
            return;
        }
        Schema::table('sh_order_shippings', function (Blueprint $table): void {
            if (Schema::hasColumn('sh_order_shippings', 'tracking_url')) {
                $table->dropColumn('tracking_url');
            }
            if (Schema::hasColumn('sh_order_shippings', 'tracking_number')) {
                $table->dropColumn('tracking_number');
            }
        });
    }
};
