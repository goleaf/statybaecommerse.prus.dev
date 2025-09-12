<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('campaign_translations')) {
            Schema::table('campaign_translations', function (Blueprint $table) {
                // Add missing translation fields
                if (!Schema::hasColumn('campaign_translations', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }
                if (!Schema::hasColumn('campaign_translations', 'subject')) {
                    $table->string('subject')->nullable()->after('description');
                }
                if (!Schema::hasColumn('campaign_translations', 'content')) {
                    $table->text('content')->nullable()->after('subject');
                }
                if (!Schema::hasColumn('campaign_translations', 'cta_text')) {
                    $table->string('cta_text')->nullable()->after('content');
                }
                if (!Schema::hasColumn('campaign_translations', 'banner_alt_text')) {
                    $table->string('banner_alt_text')->nullable()->after('cta_text');
                }
                if (!Schema::hasColumn('campaign_translations', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('banner_alt_text');
                }
                if (!Schema::hasColumn('campaign_translations', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }

                // Add indexes for performance
                $table->index(['campaign_id', 'locale'], 'campaign_translations_campaign_locale_idx');
                $table->index(['slug'], 'campaign_translations_slug_idx');
            });
        }

        // Create pivot tables for many-to-many relationships
        if (!Schema::hasTable('campaign_categories')) {
            Schema::create('campaign_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();
                
                $table->unique(['campaign_id', 'category_id'], 'campaign_categories_unique');
                $table->index(['campaign_id'], 'campaign_categories_campaign_idx');
                $table->index(['category_id'], 'campaign_categories_category_idx');
            });
        }

        if (!Schema::hasTable('campaign_products')) {
            Schema::create('campaign_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();
                
                $table->unique(['campaign_id', 'product_id'], 'campaign_products_unique');
                $table->index(['campaign_id'], 'campaign_products_campaign_idx');
                $table->index(['product_id'], 'campaign_products_product_idx');
            });
        }

        if (!Schema::hasTable('campaign_customer_groups')) {
            Schema::create('campaign_customer_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();
                $table->timestamps();
                
                $table->unique(['campaign_id', 'customer_group_id'], 'campaign_customer_groups_unique');
                $table->index(['campaign_id'], 'campaign_customer_groups_campaign_idx');
                $table->index(['customer_group_id'], 'campaign_customer_groups_group_idx');
            });
        }

        if (!Schema::hasTable('campaign_discount')) {
            Schema::create('campaign_discount', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
                $table->timestamps();
                
                $table->unique(['campaign_id', 'discount_id'], 'campaign_discount_unique');
                $table->index(['campaign_id'], 'campaign_discount_campaign_idx');
                $table->index(['discount_id'], 'campaign_discount_discount_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_discount');
        Schema::dropIfExists('campaign_customer_groups');
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaign_categories');
        
        if (Schema::hasTable('campaign_translations')) {
            Schema::table('campaign_translations', function (Blueprint $table) {
                $columns = [
                    'slug', 'subject', 'content', 'cta_text', 'banner_alt_text', 'meta_title', 'meta_description'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('campaign_translations', $column)) {
                        $table->dropColumn($column);
                    }
                }
                
                // Drop indexes
                try { $table->dropIndex('campaign_translations_campaign_locale_idx'); } catch (\Throwable $e) {}
                try { $table->dropIndex('campaign_translations_slug_idx'); } catch (\Throwable $e) {}
            });
        }
    }
};

