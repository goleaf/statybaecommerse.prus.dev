<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            // Add additional fields for enhanced functionality
            $table->string('placeholder')->nullable()->after('help_text');
            $table->text('tooltip')->nullable()->after('placeholder');
            $table->json('metadata')->nullable()->after('options');
            $table->string('validation_message')->nullable()->after('validation_rules');
            $table->boolean('is_cacheable')->default(true)->after('is_active');
            $table->integer('cache_ttl')->default(3600)->after('is_cacheable');  // Cache TTL in seconds
            $table->string('cache_key')->nullable()->after('cache_ttl');  // Custom cache key
            $table->string('environment')->default('all')->after('cache_ttl');  // all, production, staging, development
            $table->json('tags')->nullable()->after('environment');
            $table->string('version')->default('1.0.0')->after('tags');
            $table->timestamp('last_accessed_at')->nullable()->after('version');
            $table->integer('access_count')->default(0)->after('last_accessed_at');

            // Add indexes for performance
            $table->index(['is_cacheable', 'is_active']);
            $table->index(['environment', 'is_active']);
            $table->index('last_accessed_at');
            $table->index('access_count');
        });

        Schema::table('system_setting_categories', function (Blueprint $table): void {
            // Add additional fields for categories
            $table->string('template')->nullable()->after('color');
            $table->json('metadata')->nullable()->after('template');
            $table->boolean('is_collapsible')->default(true)->after('metadata');
            $table->boolean('show_in_sidebar')->default(true)->after('is_collapsible');
            $table->string('permission')->nullable()->after('show_in_sidebar');
            $table->json('tags')->nullable()->after('permission');

            // Add indexes
            $table->index(['is_collapsible', 'is_active']);
            $table->index(['show_in_sidebar', 'is_active']);
            $table->index('permission');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropIndex(['is_cacheable', 'is_active']);
            $table->dropIndex(['environment', 'is_active']);
            $table->dropIndex('last_accessed_at');
            $table->dropIndex('access_count');

            $table->dropColumn([
                'placeholder',
                'tooltip',
                'metadata',
                'validation_message',
                'is_cacheable',
                'cache_ttl',
                'cache_key',
                'environment',
                'tags',
                'version',
                'last_accessed_at',
                'access_count',
            ]);
        });

        Schema::table('system_setting_categories', function (Blueprint $table): void {
            $table->dropIndex(['is_collapsible', 'is_active']);
            $table->dropIndex(['show_in_sidebar', 'is_active']);
            $table->dropIndex('permission');

            $table->dropColumn([
                'template',
                'metadata',
                'is_collapsible',
                'show_in_sidebar',
                'permission',
                'tags',
            ]);
        });
    }
};
