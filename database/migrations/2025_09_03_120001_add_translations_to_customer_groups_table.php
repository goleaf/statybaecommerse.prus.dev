<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            // Add JSON columns for translations
            $table->json('name')->nullable()->change();
            $table->json('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            // Revert to string columns
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }
};
