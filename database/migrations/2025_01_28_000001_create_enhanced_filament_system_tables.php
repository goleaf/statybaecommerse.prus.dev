<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhanced Settings Table
        if (! Schema::hasTable('enhanced_settings')) {
            Schema::create('enhanced_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('group')->default('general');
                // Store the raw key without a unique constraint so we can scope uniqueness by locale later.
                $table->string('key');
                // Track the locale in the primary table so multilingual settings can co-exist without collisions.
                $table->string('locale', 10)->default('lt')->after('key');
                $table->json('value')->nullable();
                $table->string('type')->default('text'); // text, number, boolean, json, array
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->boolean('is_encrypted')->default(false);
                // Flag whether the setting is currently active so admin filters can ignore archived rows.
                $table->boolean('is_active')->default(true);
                $table->json('validation_rules')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['group', 'key']);
                $table->index(['is_public']);
                // Speed up locale scoped lookups that power admin filters and tests.
                $table->index('locale', 'enhanced_settings_locale_index');
                // Ensure each key is unique per locale to keep translated variants isolated.
                $table->unique(['key', 'locale'], 'enhanced_settings_key_locale_unique');
            });
        }

        if (! Schema::hasTable('enhanced_settings_translations')) {
            Schema::create('enhanced_settings_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('enhanced_setting_id');
                // Mirror the parent locale so translations resolve correctly when eager loading.
                $table->string('locale', 10);
                $table->text('description')->nullable();
                $table->string('display_name')->nullable();
                $table->text('help_text')->nullable();
                $table->timestamps();

                $table->index('locale', 'enhanced_settings_translations_locale_index');
                // Prevent duplicate translations for the same locale on a single setting.
                $table->unique(['enhanced_setting_id', 'locale'], 'enhanced_settings_translations_setting_locale_unique');
                $table->foreign('enhanced_setting_id')->references('id')->on('enhanced_settings')->onDelete('cascade');
            });
        }

        // Enhanced Media Management
        if (! Schema::hasTable('media_collections')) {
            Schema::create('media_collections', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->json('allowed_mime_types')->nullable();
                $table->bigInteger('max_file_size')->nullable(); // in bytes
                $table->integer('max_files')->nullable();
                $table->json('conversions')->nullable();
                $table->boolean('is_private')->default(false);
                $table->timestamps();
            });
        }

        // Enhanced Notification System
        if (! Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type'); // email, sms, push, database
                $table->string('event'); // order_created, user_registered, etc.
                $table->json('subject')->nullable(); // multilingual
                $table->json('content'); // multilingual
                $table->json('variables')->nullable(); // available variables
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Enhanced Audit Log
        if (! Schema::hasTable('system_logs')) {
            Schema::create('system_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('level'); // info, warning, error, critical
                $table->string('channel')->default('system');
                $table->text('message');
                $table->json('context')->nullable();
                $table->json('extra')->nullable();
                $table->string('user_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('logged_at');
                $table->timestamps();

                $table->index(['level', 'logged_at']);
                $table->index(['channel', 'logged_at']);
                $table->index(['user_id']);
            });
        }

        // Enhanced Feature Flags
        if (! Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(false);
                $table->json('conditions')->nullable(); // user groups, dates, etc.
                $table->json('rollout_percentage')->nullable(); // gradual rollout
                $table->string('environment')->nullable(); // production, staging, etc.
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }

        // Enhanced Cache Management
        if (! Schema::hasTable('cache_tags')) {
            Schema::create('cache_tags', function (Blueprint $table): void {
                $table->id();
                $table->string('tag');
                $table->string('key');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->unique(['tag', 'key']);
                $table->index(['tag']);
                $table->index(['expires_at']);
            });
        }

        // Enhanced Performance Monitoring
        if (! Schema::hasTable('performance_metrics')) {
            Schema::create('performance_metrics', function (Blueprint $table): void {
                $table->id();
                $table->string('metric_name');
                $table->string('metric_type'); // counter, gauge, histogram
                $table->decimal('value', 15, 6);
                $table->json('tags')->nullable(); // additional metadata
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['metric_name', 'recorded_at']);
                $table->index(['metric_type']);
            });
        }

        // Enhanced Background Jobs
        if (! Schema::hasTable('job_batches_extended')) {
            Schema::create('job_batches_extended', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->json('failed_job_ids');
                $table->json('options')->nullable();
                $table->json('progress')->nullable(); // custom progress tracking
                $table->json('results')->nullable(); // store results
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();

                $table->index(['name', 'created_at']);
            });
        }

        // Enhanced Multi-tenant Support
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('domain')->nullable();
                $table->json('config')->nullable(); // tenant-specific config
                $table->json('features')->nullable(); // enabled features
                $table->boolean('is_active')->default(true);
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tenant_users')) {
            Schema::create('tenant_users', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->json('roles')->nullable();
                $table->json('permissions')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('job_batches_extended');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('cache_tags');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('media_collections');
        // Drop translations before the parent table to avoid foreign key complaints during teardown.
        Schema::dropIfExists('enhanced_settings_translations');
        Schema::dropIfExists('enhanced_settings');
    }
};
