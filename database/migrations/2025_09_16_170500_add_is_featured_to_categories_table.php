<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            if (!Schema::hasColumn('categories', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_visible');
            }
            try {
                $table->index(['is_featured']);
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            try {
                $table->dropIndex(['is_featured']);
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('categories', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
