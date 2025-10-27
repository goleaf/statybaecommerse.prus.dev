<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventories')) {
            return;
        }

        if (Schema::hasColumn('inventories', 'sku')) {
            return;
        }

        Schema::table('inventories', function (Blueprint $table): void {
            $table->string('sku')->nullable()->after('product_id');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventories')) {
            return;
        }

        if (! Schema::hasColumn('inventories', 'sku')) {
            return;
        }

        Schema::table('inventories', function (Blueprint $table): void {
            $table->dropIndex(['sku']);
            $table->dropColumn('sku');
        });
    }
};
