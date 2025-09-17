<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Enhanced Settings Table (forward-only)
        Schema::create('enhanced_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');  // text, number, boolean, json, array
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->json('validation_rules')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'key']);
            $table->index(['is_public']);
        });

        // Enhanced Media Management
        Schema::create('media_collections', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('allowed_mime_types')->nullable();
            $table->bigInteger('max_file_size')->nullable();  // in bytes
            $table->integer('max_files')->nullable();
            $table->json('conversions')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });

        // Enhanced Notification System
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');  // email, sms, push, database
            $table->string('event');  // order_created, user_registered, etc.
            $table->json('subject')->nullable();  // multilingual
            $table->json('content');  // multilingual
            $table->json('variables')->nullable();  // available variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Enhanced Audit Log
        Schema::create('system_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('level');  // info, warning, error, critical
            $table->string('channel')->default('system');
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['level', 'logged_at']);
            $table->index(['channel', 'logged_at']);
            $table->index(['user_id']);
        });

        // Enhanced Feature Flags
        Schema::create('feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('conditions')->nullable();  // user groups, dates, etc.
            $table->json('rollout_percentage')->nullable();  // gradual rollout
            $table->string('environment')->nullable();  // production, staging, etc.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        // Enhanced Cache Management
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

        // Enhanced Performance Monitoring
        Schema::create('performance_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('metric_name');
            $table->string('metric_type');  // counter, gauge, histogram
            $table->decimal('value', 15, 6);
            $table->json('tags')->nullable();  // additional metadata
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['metric_name', 'recorded_at']);
            $table->index(['metric_type']);
        });

        // Enhanced Background Jobs
        Schema::create('job_batches_extended', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->json('failed_job_ids');
            $table->json('options')->nullable();  // custom progress tracking
            $table->json('progress')->nullable();  // custom progress tracking
            $table->json('results')->nullable();  // store results
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();

            $table->index(['name', 'created_at']);
        });

        // Enhanced API Management
        Schema::create('api_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('secret')->nullable();
            $table->json('permissions')->nullable();  // scoped permissions
            $table->json('rate_limits')->nullable();  // custom rate limits
            $table->string('user_id')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Enhanced Multi-tenant Support
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable();
            $table->json('config')->nullable();  // tenant-specific config
            $table->json('features')->nullable();  // enabled features
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

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

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('job_batches_extended');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('cache_tags');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('media_collections');
        Schema::dropIfExists('enhanced_settings');
    }
};
