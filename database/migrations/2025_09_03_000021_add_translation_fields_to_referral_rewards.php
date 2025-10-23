<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            if (! Schema::hasColumn('referral_rewards', 'title')) {
                $table->json('title')->nullable()->after('type');
            }
            if (! Schema::hasColumn('referral_rewards', 'description')) {
                $table->json('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('referral_rewards', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (! Schema::hasColumn('referral_rewards', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }
            if (! Schema::hasColumn('referral_rewards', 'conditions')) {
                $table->json('conditions')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('referral_rewards', 'reward_data')) {
                $table->json('reward_data')->nullable()->after('conditions');
            }
            // Index creation is skipped if columns already exist to avoid duplicate index errors in SQLite during tests.
        });
    }

    public function down(): void
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'status']);
            $table->dropIndex(['priority', 'created_at']);
            $table->dropIndex(['type', 'is_active']);

            $table->dropColumn([
                'title',
                'description',
                'is_active',
                'priority',
                'conditions',
                'reward_data',
            ]);
        });
    }
};
