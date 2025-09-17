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
        Schema::table('users', function (Blueprint $table) {
            // Multi-translation support fields
            $table->string('website')->nullable()->after('email');
            $table->json('bio')->nullable()->after('website');
            $table->json('company')->nullable()->after('bio');
            $table->json('position')->nullable()->after('company');

            // Additional user fields
            $table->json('social_links')->nullable()->after('website');
            $table->json('notification_preferences')->nullable()->after('social_links');
            $table->json('privacy_settings')->nullable()->after('notification_preferences');
            $table->json('marketing_preferences')->nullable()->after('privacy_settings');
            $table->integer('login_count')->default(0)->after('marketing_preferences');
            $table->timestamp('last_activity_at')->nullable()->after('login_count');
            $table->timestamp('phone_verified_at')->nullable()->after('last_activity_at');
            $table->text('two_factor_secret')->nullable()->after('phone_verified_at');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->string('api_token', 80)->nullable()->unique()->after('two_factor_confirmed_at');
            $table->string('stripe_customer_id')->nullable()->after('api_token');
            $table->string('stripe_account_id')->nullable()->after('stripe_customer_id');
            $table->string('subscription_status')->nullable()->after('stripe_account_id');
            $table->string('subscription_plan')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_plan');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_ends_at');
            $table->string('status')->default('active')->after('trial_ends_at');
            $table->string('verification_token')->nullable()->after('status');
            $table->string('password_reset_token')->nullable()->after('verification_token');
            $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'company',
                'position',
                'website',
                'social_links',
                'notification_preferences',
                'privacy_settings',
                'marketing_preferences',
                'login_count',
                'last_activity_at',
                'phone_verified_at',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'api_token',
                'stripe_customer_id',
                'stripe_account_id',
                'subscription_status',
                'subscription_plan',
                'subscription_ends_at',
                'trial_ends_at',
                'status',
                'verification_token',
                'password_reset_token',
                'password_reset_expires_at',
            ]);
        });
    }
};

