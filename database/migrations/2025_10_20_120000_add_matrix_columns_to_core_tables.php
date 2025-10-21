<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'permissions_matrix')) {
                $table->json('permissions_matrix')->nullable()->after('marketing_preferences');
            }
        });

        Schema::table('shipping_options', function (Blueprint $table): void {
            if (! Schema::hasColumn('shipping_options', 'shipping_matrix')) {
                $table->json('shipping_matrix')->nullable()->after('metadata');
            }
        });

        Schema::table('channels', function (Blueprint $table): void {
            if (! Schema::hasColumn('channels', 'payment_matrix')) {
                $table->json('payment_matrix')->nullable()->after('payment_methods');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'variant_attribute_matrix')) {
                $table->json('variant_attribute_matrix')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'permissions_matrix')) {
                $table->dropColumn('permissions_matrix');
            }
        });

        Schema::table('shipping_options', function (Blueprint $table): void {
            if (Schema::hasColumn('shipping_options', 'shipping_matrix')) {
                $table->dropColumn('shipping_matrix');
            }
        });

        Schema::table('channels', function (Blueprint $table): void {
            if (Schema::hasColumn('channels', 'payment_matrix')) {
                $table->dropColumn('payment_matrix');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'variant_attribute_matrix')) {
                $table->dropColumn('variant_attribute_matrix');
            }
        });
    }
};
