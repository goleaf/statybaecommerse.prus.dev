<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            // Add translatable fields
            $table->json('name')->nullable()->after('variant_id');
            $table->json('description')->nullable()->after('name');
            $table->json('notes')->nullable()->after('description');

            // Add additional fields for enhanced functionality
            $table->boolean('is_active')->default(true)->after('notes');
            $table->integer('priority')->default(100)->after('is_active');
            $table->integer('min_quantity')->nullable()->after('priority');
            $table->integer('max_quantity')->nullable()->after('min_quantity');
            $table->timestamp('valid_from')->nullable()->after('max_quantity');
            $table->timestamp('valid_until')->nullable()->after('valid_from');

            // Add compare_amount
            $table->decimal('compare_amount', 15, 2)->nullable()->after('net_amount');

            // Add indexes for performance
            $table->index(['is_active', 'priority']);
            $table->index(['valid_from', 'valid_until']);
            $table->index(['min_quantity', 'max_quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'priority']);
            $table->dropIndex(['valid_from', 'valid_until']);
            $table->dropIndex(['min_quantity', 'max_quantity']);

            $table->dropColumn([
                'name',
                'description',
                'notes',
                'is_active',
                'priority',
                'min_quantity',
                'max_quantity',
                'valid_from',
                'valid_until',
                'compare_amount',
            ]);
        });
    }
};
