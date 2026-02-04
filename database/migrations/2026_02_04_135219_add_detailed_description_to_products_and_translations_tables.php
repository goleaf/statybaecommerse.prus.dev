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
        Schema::table('products', function (Blueprint $table) {
            $table->text('detailed_description')->nullable()->after('description');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            $table->text('detailed_description')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('detailed_description');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropColumn('detailed_description');
        });
    }
};
