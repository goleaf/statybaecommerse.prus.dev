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
            $table->boolean('is_venipak_locker_excluded')->default(false)->after('is_active');
            $table->boolean('is_venipak_courier_excluded')->default(false)->after('is_venipak_locker_excluded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_venipak_locker_excluded', 'is_venipak_courier_excluded']);
        });
    }
};
