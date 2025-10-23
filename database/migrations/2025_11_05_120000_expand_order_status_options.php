<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand the order status enum so seeded data no longer fails validation.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            // Early return keeps deployment scripts idempotent when the table is missing.
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE `orders`
            MODIFY COLUMN `status` ENUM(
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'completed',
                'cancelled',
                'refunded',
                'returned'
            ) DEFAULT 'pending'
        SQL);
    }

    /**
     * Restore the previous enum definition used before this adjustment.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            // Consistent with the up() guard, skip when the orders table is missing.
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE `orders`
            MODIFY COLUMN `status` ENUM(
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled'
            ) DEFAULT 'pending'
        SQL);
    }
};
