<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                if (!Schema::hasColumn('product_variants', 'status')) {
                    $table->string('status', 32)->default('active');
                }
                try {
                    $table->index('status', 'product_variants_status_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                try {
                    $table->dropIndex('product_variants_status_idx');
                } catch (\Throwable $e) {
                }
                if (Schema::hasColumn('product_variants', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
