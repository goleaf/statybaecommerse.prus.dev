<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->json('title')->nullable()->after('user_agent');
            $table->json('description')->nullable()->after('title');
            $table->json('terms_conditions')->nullable()->after('description');
            $table->json('benefits_description')->nullable()->after('terms_conditions');
            $table->json('how_it_works')->nullable()->after('benefits_description');
            $table->json('seo_title')->nullable()->after('how_it_works');
            $table->json('seo_description')->nullable()->after('seo_title');
            $table->json('seo_keywords')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description',
                'terms_conditions',
                'benefits_description',
                'how_it_works',
                'seo_title',
                'seo_description',
                'seo_keywords',
            ]);
        });
    }
};
