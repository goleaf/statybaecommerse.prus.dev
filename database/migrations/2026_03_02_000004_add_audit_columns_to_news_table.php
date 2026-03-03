<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            if (! Schema::hasColumn('news', 'created_by_id')) {
                if (Schema::hasTable('admin_users')) {
                    $table->foreignId('created_by_id')->nullable()->after('approved_by_id')->constrained('admin_users')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('created_by_id')->nullable()->after('approved_by_id');
                }

                $table->index('created_by_id');
            }

            if (! Schema::hasColumn('news', 'updated_by_id')) {
                if (Schema::hasTable('admin_users')) {
                    $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('admin_users')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('updated_by_id')->nullable()->after('created_by_id');
                }

                $table->index('updated_by_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            if (Schema::hasColumn('news', 'updated_by_id')) {
                try {
                    $table->dropForeign(['updated_by_id']);
                } catch (\Throwable) {
                    // Ignore when foreign key does not exist on older environments.
                }

                try {
                    $table->dropIndex(['updated_by_id']);
                } catch (\Throwable) {
                    // Ignore when index does not exist.
                }

                $table->dropColumn('updated_by_id');
            }

            if (Schema::hasColumn('news', 'created_by_id')) {
                try {
                    $table->dropForeign(['created_by_id']);
                } catch (\Throwable) {
                    // Ignore when foreign key does not exist on older environments.
                }

                try {
                    $table->dropIndex(['created_by_id']);
                } catch (\Throwable) {
                    // Ignore when index does not exist.
                }

                $table->dropColumn('created_by_id');
            }
        });
    }
};
