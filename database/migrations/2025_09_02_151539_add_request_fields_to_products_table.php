<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_requestable')) {
                $table->boolean('is_requestable')->default(false)->after('is_featured');
            }
            if (!Schema::hasColumn('products', 'requests_count')) {
                $table->integer('requests_count')->default(0)->after('is_requestable');
            }
            if (!Schema::hasColumn('products', 'minimum_quantity')) {
                $table->integer('minimum_quantity')->default(1)->after('requests_count');
            }
            if (!Schema::hasColumn('products', 'hide_add_to_cart')) {
                $table->boolean('hide_add_to_cart')->default(false)->after('minimum_quantity');
            }
            if (!Schema::hasColumn('products', 'request_message')) {
                $table->text('request_message')->nullable()->after('hide_add_to_cart');
            }

            try {
                $table->index(['is_requestable', 'requests_count']);
            } catch (\Throwable $e) {
                // ignore if index exists
            }

            try {
                $table->index(['hide_add_to_cart']);
            } catch (\Throwable $e) {
                // ignore if index exists
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropIndex(['is_requestable', 'requests_count']);
            } catch (\Throwable $e) {
                // ignore if index missing
            }

            try {
                $table->dropIndex(['hide_add_to_cart']);
            } catch (\Throwable $e) {
                // ignore if index missing
            }

            if (Schema::hasColumn('products', 'request_message')) {
                $table->dropColumn('request_message');
            }
            if (Schema::hasColumn('products', 'hide_add_to_cart')) {
                $table->dropColumn('hide_add_to_cart');
            }
            if (Schema::hasColumn('products', 'minimum_quantity')) {
                $table->dropColumn('minimum_quantity');
            }
            if (Schema::hasColumn('products', 'requests_count')) {
                $table->dropColumn('requests_count');
            }
            if (Schema::hasColumn('products', 'is_requestable')) {
                $table->dropColumn('is_requestable');
            }
        });
    }
};
