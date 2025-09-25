<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recommendation_blocks')) {
            Schema::table('recommendation_blocks', function (Blueprint $table) {
                if (! Schema::hasColumn('recommendation_blocks', 'show_title')) {
                    $table->boolean('show_title')->default(true)->after('is_active');
                }
                if (! Schema::hasColumn('recommendation_blocks', 'show_description')) {
                    $table->boolean('show_description')->default(false)->after('show_title');
                }
                if (! Schema::hasColumn('recommendation_blocks', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('show_description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recommendation_blocks')) {
            Schema::table('recommendation_blocks', function (Blueprint $table) {
                if (Schema::hasColumn('recommendation_blocks', 'is_default')) {
                    $table->dropColumn('is_default');
                }
                if (Schema::hasColumn('recommendation_blocks', 'show_description')) {
                    $table->dropColumn('show_description');
                }
                if (Schema::hasColumn('recommendation_blocks', 'show_title')) {
                    $table->dropColumn('show_title');
                }
            });
        }
    }
};

