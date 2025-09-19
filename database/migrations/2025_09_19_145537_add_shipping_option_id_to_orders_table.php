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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_option_id')->nullable()->after('zone_id');
            $table->foreign('shipping_option_id')->references('id')->on('shipping_options')->onDelete('set null');
            $table->index('shipping_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_option_id']);
            $table->dropIndex(['shipping_option_id']);
            $table->dropColumn('shipping_option_id');
        });
    }
};
