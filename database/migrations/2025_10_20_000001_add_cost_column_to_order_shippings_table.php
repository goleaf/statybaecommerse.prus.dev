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
            if (! Schema::hasColumn('order_shippings', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('weight');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            if (Schema::hasColumn('order_shippings', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }
};
