<?php

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
        Schema::table('products', function (Blueprint $table) {
            // Add fields for product requests
            $table->boolean('is_requestable')->default(false)->after('is_featured');
            $table->integer('requests_count')->default(0)->after('is_requestable');
            $table->integer('minimum_quantity')->default(1)->after('requests_count');
            $table->boolean('hide_add_to_cart')->default(false)->after('minimum_quantity');
            $table->text('request_message')->nullable()->after('hide_add_to_cart');

            // Add indexes for performance
            $table->index(['is_requestable', 'requests_count']);
            $table->index(['hide_add_to_cart']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_requestable', 'requests_count']);
            $table->dropIndex(['hide_add_to_cart']);

            $table->dropColumn([
                'is_requestable',
                'requests_count',
                'minimum_quantity',
                'hide_add_to_cart',
                'request_message',
            ]);
        });
    }
};
