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
            if (! Schema::hasColumn('users', 'website')) {
                $table->string('website')->nullable()->after('email');
            }

            // Multi-translation support fields
            if (! Schema::hasColumn('users', 'bio')) {
                $table->json('bio')->nullable()->after('website');
            }
            if (! Schema::hasColumn('users', 'company')) {
                $table->json('company')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('users', 'position')) {
                $table->json('position')->nullable()->after('company');
            }

            // Additional user fields
            if (! Schema::hasColumn('users', 'social_links')) {
                $table->json('social_links')->nullable()->after('website');
            }
            if (! Schema::hasColumn('users', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable()->after('social_links');
            }
            if (! Schema::hasColumn('users', 'privacy_settings')) {
                $table->json('privacy_settings')->nullable()->after('notification_preferences');
            }
            if (! Schema::hasColumn('users', 'marketing_preferences')) {
                $table->json('marketing_preferences')->nullable()->after('privacy_settings');
            }
            if (! Schema::hasColumn('users', 'login_count')) {
                $table->integer('login_count')->default(0)->after('marketing_preferences');
            }
            if (! Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('login_count');
            }
            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('last_activity_at');
            }
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('phone_verified_at');
            }
            if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
            if (! Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->nullable()->unique()->after('two_factor_confirmed_at');
            }
            if (! Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('api_token');
            }
            if (! Schema::hasColumn('users', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('stripe_customer_id');
            }
            if (! Schema::hasColumn('users', 'subscription_status')) {
                $table->string('subscription_status')->nullable()->after('stripe_account_id');
            }
            if (! Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan')->nullable()->after('subscription_status');
            }
            if (! Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('subscription_plan');
            }
            if (! Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_ends_at');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('trial_ends_at');
            }
            if (! Schema::hasColumn('users', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'password_reset_token')) {
                $table->string('password_reset_token')->nullable()->after('verification_token');
            }
            if (! Schema::hasColumn('users', 'password_reset_expires_at')) {
                $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_token');
            }
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
