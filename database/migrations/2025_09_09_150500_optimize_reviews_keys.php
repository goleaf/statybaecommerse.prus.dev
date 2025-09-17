<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table): void {
            // Helpful composite indexes for common query patterns
            // Names chosen to avoid collisions with existing indexes
            $table->index(['user_id', 'created_at'], 'reviews_user_created_idx');
            $table->index(['product_id', 'locale', 'is_approved', 'created_at'], 'reviews_prod_loc_approved_created_idx');
            $table->index(['product_id', 'rating'], 'reviews_prod_rating_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table): void {
            // Drop by explicit names to be SQLite/MySQL friendly
            try {
                $table->dropIndex('reviews_user_created_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('reviews_prod_loc_approved_created_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('reviews_prod_rating_idx');
            } catch (\Throwable $e) {
            }
        });
    }
};
