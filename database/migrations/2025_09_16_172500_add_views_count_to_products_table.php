<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (!Schema::hasColumn('products', 'views_count')) {
                $table->integer('views_count')->default(0)->after('status');
            }
            try {
                $table->index(['views_count']);
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            try {
                $table->dropIndex(['views_count']);
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('products', 'views_count')) {
                $table->dropColumn('views_count');
            }
        });
    }
};
