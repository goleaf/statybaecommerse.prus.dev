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
        // Drop NewsCategory related tables
        Schema::dropIfExists('news_category_pivot');
        Schema::dropIfExists('news_category_translations');
        Schema::dropIfExists('news_categories');

        // Drop NewsApproval table
        Schema::dropIfExists('news_approvals');

        // Remove moderation columns from news table if they exist
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn([
                    'moderation_status',
                    'moderation_notes',
                    'moderated_at',
                    'moderated_by',
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we're cleaning up unused models
        // If reversal is needed, restore from backup or recreate tables manually
    }
};
