<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('discount_customer_groups')) {
            Schema::create('discount_customer_groups', function (Blueprint $table) {
                $table->id();
                $table
                    ->foreignId('discount_id')
                    ->constrained('discounts')
                    ->cascadeOnDelete();
                $table
                    ->foreignId('customer_group_id')
                    ->constrained('customer_groups')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['discount_id', 'customer_group_id'], 'discount_customer_group_unique');
                $table->index('discount_id', 'discount_customer_groups_discount_idx');
                $table->index('customer_group_id', 'discount_customer_groups_group_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_customer_groups');
    }
};
