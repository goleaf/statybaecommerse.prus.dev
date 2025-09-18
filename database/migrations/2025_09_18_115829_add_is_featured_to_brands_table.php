<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_enabled');
            $table->index(['is_featured', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'is_enabled']);
            $table->dropColumn('is_featured');
        });
    }
};