<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            // Remove JSON casts and make fields regular strings for translation system
            $table->string('name')->change();
            $table->text('description')->nullable()->after('name');
            // sort_order already exists in the original table creation
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['description']);
            $table->dropSoftDeletes();
        });
    }
};
