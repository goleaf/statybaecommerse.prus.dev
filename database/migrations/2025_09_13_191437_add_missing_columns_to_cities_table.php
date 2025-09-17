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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('density', 10, 2)->nullable();
            $table->decimal('elevation', 10, 2)->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_symbol', 5)->nullable();
            $table->string('language_code', 5)->nullable();
            $table->string('language_name')->nullable();
            $table->string('phone_code', 10)->nullable();
            $table->string('postal_code', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'area',
                'density',
                'elevation',
                'timezone',
                'currency_code',
                'currency_symbol',
                'language_code',
                'language_name',
                'phone_code',
                'postal_code',
            ]);
        });
    }
};