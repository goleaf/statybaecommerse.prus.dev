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
        Schema::table('zone_translations', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('meta_keywords')->nullable()->after('meta_description');
            $table->text('short_description')->nullable()->after('meta_keywords');
            $table->longText('long_description')->nullable()->after('short_description');

            // Add indexes for better performance
            $table->index(['zone_id', 'locale']);
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zone_translations', function (Blueprint $table) {
            $table->dropIndex(['zone_id', 'locale']);
            $table->dropIndex('locale');

            $table->dropColumn([
                'meta_title',
                'meta_description',
                'meta_keywords',
                'short_description',
                'long_description',
            ]);
        });
    }
};

