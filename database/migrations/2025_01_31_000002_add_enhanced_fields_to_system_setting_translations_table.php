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
        Schema::table('system_setting_translations', function (Blueprint $table) {
            $table->text('rich_description')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_setting_translations', function (Blueprint $table) {
            $table->dropColumn([
                'rich_description',
                'attachments',
                'is_active',
                'is_public',
                'metadata',
                'tags',
                'sort_order',
                'deleted_at',
            ]);
        });
    }
};
