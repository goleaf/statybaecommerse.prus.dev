<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('customer_groups', 'color')) {
                $table->string('color', 32)->nullable()->after('code');
            }

            if (! Schema::hasColumn('customer_groups', 'icon')) {
                $table->string('icon', 64)->nullable()->after('color');
            }

            if (! Schema::hasColumn('customer_groups', 'minimum_order_amount')) {
                $table->decimal('minimum_order_amount', 12, 2)->default(0)->after('discount_fixed');
            }

            if (! Schema::hasColumn('customer_groups', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('minimum_order_amount');
            }

            if (! Schema::hasColumn('customer_groups', 'payment_terms')) {
                $table->string('payment_terms', 32)->default('net_30')->after('credit_limit');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table): void {
            $columns = [
                'color',
                'icon',
                'minimum_order_amount',
                'credit_limit',
                'payment_terms',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('customer_groups', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
