<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            $table->json('title')->nullable()->after('type');
            $table->json('description')->nullable()->after('title');
            $table->boolean('is_active')->default(true)->after('description');
            $table->integer('priority')->default(0)->after('is_active');
            $table->json('conditions')->nullable()->after('priority');
            $table->json('reward_data')->nullable()->after('conditions');

            $table->index(['is_active', 'status']);
            $table->index(['priority', 'created_at']);
            $table->index(['type', 'is_active']);
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

