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
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title')->nullable();
            $table->string('button_color')->default('#007bff')->after('text_color');
            $table->string('text_alignment')->default('center')->after('button_color');
            $table->string('content_position')->default('center')->after('text_alignment');
            $table->string('priority')->default('normal')->after('sort_order');
            $table->json('tags')->nullable()->after('priority');
            $table->json('custom_attributes')->nullable()->after('tags');
            $table->json('target_audience')->nullable()->after('custom_attributes');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_scheduled')->default(false)->after('is_featured');
            $table->timestamp('start_date')->nullable()->after('is_scheduled');
            $table->timestamp('end_date')->nullable()->after('start_date');
            $table->json('slides')->nullable()->after('settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'button_color',
                'text_alignment',
                'content_position',
                'priority',
                'tags',
                'custom_attributes',
                'target_audience',
                'is_featured',
                'is_scheduled',
                'start_date',
                'end_date',
                'slides',
            ]);
        });
    }
};