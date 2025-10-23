<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait InteractsWithFilamentPivotTables
{
    private static bool $pivotTestSchemaMigrated = false;

    protected function ensureFilamentPivotTablesMigrated(): void
    {
        if (
            ! self::$pivotTestSchemaMigrated
            || ! Schema::hasTable('news_categories')
            || ! Schema::hasTable('news')
            || ! Schema::hasTable('news_category_translations')
        ) {
            $this->createFilamentPivotTestSchema();
            self::$pivotTestSchemaMigrated = true;
        }
    }

    protected function resetFilamentPivotTables(): void
    {
        foreach ([
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'roles',
            'news_category_pivot',
            'news_tag_pivot',
            'recommendation_block_products',
            'news_translations',
            'news_category_translations',
            'news_tag_translations',
            'news',
            'news_categories',
            'news_tags',
            'products',
            'recommendation_blocks',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    private function createFilamentPivotTestSchema(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->boolean('is_admin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('preferred_locale')->nullable();
                $table->rememberToken();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('guard_name')->nullable();
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('guard_name')->nullable();
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
            });
        }

        if (! Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_visible')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_breaking')->default(false);
                $table->string('moderation_state')->nullable();
                $table->timestamp('submitted_for_review_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->string('author_name')->nullable();
                $table->string('author_email')->nullable();
                $table->unsignedInteger('view_count')->default(0);
                $table->json('meta_data')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_translations')) {
            Schema::create('news_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
                $table->string('locale');
                $table->string('title')->nullable();
                $table->string('slug')->nullable();
                $table->text('summary')->nullable();
                $table->longText('content')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_categories')) {
            Schema::create('news_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('color')->nullable();
                $table->string('icon')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_category_translations')) {
            Schema::create('news_category_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('news_category_id')->constrained('news_categories')->cascadeOnDelete();
                $table->string('locale');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_category_pivot')) {
            Schema::create('news_category_pivot', function (Blueprint $table): void {
                $table->unsignedBigInteger('news_id');
                $table->unsignedBigInteger('news_category_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_tags')) {
            Schema::create('news_tags', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->string('color')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_tag_translations')) {
            Schema::create('news_tag_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('news_tag_id')->constrained('news_tags')->cascadeOnDelete();
                $table->string('locale');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_tag_pivot')) {
            Schema::create('news_tag_pivot', function (Blueprint $table): void {
                $table->unsignedBigInteger('news_id');
                $table->unsignedBigInteger('news_tag_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->string('type')->nullable();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('sku')->nullable();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->boolean('is_visible')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->boolean('manage_stock')->default(false);
                $table->unsignedInteger('stock_quantity')->default(0);
                $table->unsignedInteger('low_stock_threshold')->default(0);
                $table->decimal('weight', 10, 2)->nullable();
                $table->decimal('length', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('height', 10, 2)->nullable();
                $table->timestamp('published_at')->nullable();
                $table->string('status')->default('draft');
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recommendation_blocks')) {
            Schema::create('recommendation_blocks', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type');
                $table->string('position');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('show_title')->default(true);
                $table->boolean('show_description')->default(false);
                $table->unsignedInteger('max_products')->default(10);
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('config_ids')->nullable();
                $table->unsignedInteger('cache_duration')->default(0);
                $table->json('display_settings')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recommendation_block_products')) {
            Schema::create('recommendation_block_products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('recommendation_block_id')->constrained('recommendation_blocks')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }
}
