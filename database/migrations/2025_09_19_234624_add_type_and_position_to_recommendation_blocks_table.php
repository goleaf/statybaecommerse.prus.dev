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
        Schema::table('recommendation_blocks', function (Blueprint $table) {
            $table->string('type')->default('featured')->after('description');
            $table->string('position')->default('top')->after('type');
            $table->integer('sort_order')->default(0)->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendation_blocks', function (Blueprint $table) {
            $table->dropColumn(['type', 'position', 'sort_order']);
        });
    }
};
