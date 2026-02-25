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
        Schema::dropIfExists('seo_data');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('seo_data', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('locale')->index();
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('meta_tags')->nullable();
            $table->json('structured_data')->nullable();
            $table->integer('seo_score')->default(0);
            $table->boolean('no_index')->default(false);
            $table->boolean('no_follow')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['seoable_type', 'seoable_id', 'locale']);
        });
    }
};
