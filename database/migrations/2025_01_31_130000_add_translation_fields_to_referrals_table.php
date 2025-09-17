<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('referrals')) {
            return;
        }

        try {
            Schema::table('referrals', function (Blueprint $table) {
                if (!Schema::hasColumn('referrals', 'title')) {
                    $table->json('title')->nullable()->after('user_agent');
                }
                if (!Schema::hasColumn('referrals', 'description')) {
                    $table->json('description')->nullable()->after('title');
                }
                if (!Schema::hasColumn('referrals', 'terms_conditions')) {
                    $table->json('terms_conditions')->nullable()->after('description');
                }
                if (!Schema::hasColumn('referrals', 'benefits_description')) {
                    $table->json('benefits_description')->nullable()->after('terms_conditions');
                }
                if (!Schema::hasColumn('referrals', 'how_it_works')) {
                    $table->json('how_it_works')->nullable()->after('benefits_description');
                }
                if (!Schema::hasColumn('referrals', 'seo_title')) {
                    $table->json('seo_title')->nullable()->after('how_it_works');
                }
                if (!Schema::hasColumn('referrals', 'seo_description')) {
                    $table->json('seo_description')->nullable()->after('seo_title');
                }
                if (!Schema::hasColumn('referrals', 'seo_keywords')) {
                    $table->json('seo_keywords')->nullable()->after('seo_description');
                }
            });
        } catch (\Throwable $e) {
            // retry without AFTER positioning if needed
            Schema::table('referrals', function (Blueprint $table) {
                foreach (['title', 'description', 'terms_conditions', 'benefits_description', 'how_it_works', 'seo_title', 'seo_description', 'seo_keywords'] as $col) {
                    if (!Schema::hasColumn('referrals', $col)) {
                        $table->json($col)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('referrals')) {
            return;
        }

        Schema::table('referrals', function (Blueprint $table) {
            foreach ([
                'title',
                'description',
                'terms_conditions',
                'benefits_description',
                'how_it_works',
                'seo_title',
                'seo_description',
                'seo_keywords',
            ] as $col) {
                if (Schema::hasColumn('referrals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
