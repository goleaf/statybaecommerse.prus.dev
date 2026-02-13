<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referral_code_statistics')) {
            Schema::create('referral_code_statistics', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('referral_code_id')->constrained('referral_codes')->cascadeOnDelete();
                $table->date('date');
                $table->unsignedInteger('total_views')->default(0);
                $table->unsignedInteger('total_clicks')->default(0);
                $table->unsignedInteger('total_signups')->default(0);
                $table->unsignedInteger('total_conversions')->default(0);
                $table->decimal('total_revenue', 12, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['referral_code_id', 'date']);
                $table->index('date');
            });
        }

        if (! Schema::hasTable('referral_code_usage_logs')) {
            Schema::create('referral_code_usage_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('referral_code_id')->constrained('referral_codes')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('referrer')->nullable();
                $table->json('metadata')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['referral_code_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('referral_statistics')) {
            Schema::create('referral_statistics', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('date');
                $table->unsignedInteger('total_referrals')->default(0);
                $table->unsignedInteger('completed_referrals')->default(0);
                $table->unsignedInteger('pending_referrals')->default(0);
                $table->decimal('total_rewards_earned', 12, 2)->default(0);
                $table->decimal('total_discounts_given', 12, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'date']);
                $table->index('date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_statistics');
        Schema::dropIfExists('referral_code_usage_logs');
        Schema::dropIfExists('referral_code_statistics');
    }
};
