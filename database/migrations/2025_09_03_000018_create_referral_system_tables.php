<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Create referral_campaigns table first (used by other tables)
        if (! Schema::hasTable('referral_campaigns')) {
            Schema::create('referral_campaigns', function (Blueprint $table) {
                $table->id();
                $table->json('name');
                $table->json('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->decimal('reward_amount', 12, 2)->nullable();
                $table->string('reward_type')->nullable();
                $table->integer('max_referrals_per_user')->nullable();
                $table->integer('max_total_referrals')->nullable();
                $table->json('conditions')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'start_date', 'end_date']);
            });
        }

        // Create referral_codes table for tracking generated codes
        if (! Schema::hasTable('referral_codes')) {
            Schema::create('referral_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('code', 20)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->json('metadata')->nullable();
                $table->json('title')->nullable();
                $table->json('description')->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_count')->default(0);
                $table->decimal('reward_amount', 12, 2)->nullable();
                $table->string('reward_type')->nullable();
                $table->json('conditions')->nullable();
                $table->foreignId('campaign_id')->nullable()->constrained('referral_campaigns');
                $table->string('source')->nullable();
                $table->json('tags')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
                $table->index(['code']);
                $table->index(['campaign_id']);
                $table->index(['source']);
                $table->index(['reward_type']);
                $table->index(['is_active', 'expires_at']);
            });
        }

        // Create referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 20)->unique();
            $table->string('status')->default('pending'); // pending, completed, expired
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();

            // Additional tracking fields
            $table->string('source')->nullable(); // website, email, social, etc.
            $table->string('campaign')->nullable(); // campaign name
            $table->string('utm_source')->nullable(); // UTM tracking
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['referrer_id', 'status']);
            $table->index(['referred_id']);
            $table->index(['referral_code']);
            $table->index(['status', 'completed_at']);
            $table->index(['source']);
            $table->index(['campaign']);
            $table->index(['created_at']);
            $table->index(['expires_at']);
        });

        // Create referral_rewards table
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained('referrals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->string('type'); // referrer_bonus, referred_discount
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3)->default('EUR');
            $table->string('status')->default('pending'); // pending, applied, expired
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['referral_id']);
            $table->index(['order_id']);
            $table->index(['type', 'status']);
        });

        // Add referral fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->unique()->nullable()->after('is_admin');
            $table->timestamp('referral_code_generated_at')->nullable()->after('referral_code');
            $table->json('referral_settings')->nullable()->after('referral_code_generated_at');
        });

        // Create referral_code_usage_logs table
        Schema::create('referral_code_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained('referral_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['referral_code_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        // Create referral_code_statistics table
        Schema::create('referral_code_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained('referral_codes')->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_views')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_signups')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['referral_code_id', 'date']);
            $table->index(['date']);
        });

        // Create referral_statistics table for analytics
        Schema::create('referral_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_referrals')->default(0);
            $table->integer('completed_referrals')->default(0);
            $table->integer('pending_referrals')->default(0);
            $table->decimal('total_rewards_earned', 12, 2)->default(0);
            $table->decimal('total_discounts_given', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_statistics');
        Schema::dropIfExists('referral_code_statistics');
        Schema::dropIfExists('referral_code_usage_logs');
        Schema::dropIfExists('referral_campaigns');
        Schema::dropIfExists('referral_codes');
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referrals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_code', 'referral_code_generated_at', 'referral_settings']);
        });
    }
};
