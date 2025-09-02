<?php declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    // Ensure tables exist used by sitemap queries (minimal columns)
    $this->artisan('migrate', ['--force' => true]);
    if (! Schema::hasTable('sh_categories')) {
        Schema::create('sh_categories', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_enabled')->default(true); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_category_translations')) {
        Schema::create('sh_category_translations', function ($table) { $table->id(); $table->unsignedBigInteger('category_id'); $table->string('locale'); $table->string('slug')->nullable(); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_collections')) {
        Schema::create('sh_collections', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_enabled')->default(true); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_collection_translations')) {
        Schema::create('sh_collection_translations', function ($table) { $table->id(); $table->unsignedBigInteger('collection_id'); $table->string('locale'); $table->string('slug')->nullable(); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_brands')) {
        Schema::create('sh_brands', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_enabled')->default(true); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_brand_translations')) {
        Schema::create('sh_brand_translations', function ($table) { $table->id(); $table->unsignedBigInteger('brand_id'); $table->string('locale'); $table->string('slug')->nullable(); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_products')) {
        Schema::create('sh_products', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_visible')->default(true); $table->timestamp('published_at')->nullable(); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_product_translations')) {
        Schema::create('sh_product_translations', function ($table) { $table->id(); $table->unsignedBigInteger('product_id'); $table->string('locale'); $table->string('slug')->nullable(); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_legals')) {
        Schema::create('sh_legals', function ($table) { $table->id(); $table->string('slug')->nullable(); $table->boolean('is_enabled')->default(true); $table->timestamps(); });
    }
    if (! Schema::hasTable('sh_legal_translations')) {
        Schema::create('sh_legal_translations', function ($table) { $table->id(); $table->unsignedBigInteger('legal_id'); $table->string('locale'); $table->string('slug')->nullable(); $table->timestamps(); });
    }
});

it('serves sitemap index', function (): void {
    config()->set('app.supported_locales', 'en');

    $resp = $this->get('/sitemap.xml');

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/xml');
    $resp->assertSee('<urlset');
});

it('serves localized sitemap', function (): void {
    config()->set('app.supported_locales', 'en');

    // seed minimal entries
    DB::table('sh_categories')->insert(['slug' => 'c-1', 'is_enabled' => true]);
    DB::table('sh_collections')->insert(['slug' => 'k-1', 'is_enabled' => true]);
    DB::table('sh_brands')->insert(['slug' => 'b-1', 'is_enabled' => true]);
    DB::table('sh_products')->insert(['slug' => 'p-1', 'is_visible' => true, 'published_at' => now()]);
    DB::table('sh_legals')->insert(['slug' => 'l-1', 'is_enabled' => true]);

    $resp = $this->get('/en/sitemap.xml');
    $resp->assertOk();
    $resp->assertSee('/en/categories/c-1');
    $resp->assertSee('/en/collections/k-1');
    $resp->assertSee('/en/brands/b-1');
    $resp->assertSee('/en/products/p-1');
    $resp->assertSee('/en/legal/l-1');
});


