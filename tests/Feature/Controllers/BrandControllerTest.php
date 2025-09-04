<?php declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->artisan('migrate', ['--force' => true]);
    if (!Schema::hasTable('brands')) {
        Schema::create('brands', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }
    if (!Schema::hasTable('brand_translations')) {
        Schema::create('brand_translations', function ($table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->string('locale');
            $table->string('slug')->nullable();
            $table->timestamps();
        });
    }
    if (!Schema::hasTable('products')) {
        Schema::create('products', function ($table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
});

it('lists brands when feature enabled', function (): void {
    config()->set('app-features.features.brand', true);
    config()->set('app.supported_locales', 'en');

    DB::table('brands')->insert(['name' => 'Z', 'slug' => 'z', 'is_enabled' => true]);
    DB::table('brands')->insert(['name' => 'A', 'slug' => 'a', 'is_enabled' => true]);

    $this->get('/en/brands')->assertOk();
});

it('brand show redirects to canonical slug', function (): void {
    config()->set('app-features.features.brand', true);
    config()->set('app.supported_locales', 'en');

    $id = DB::table('brands')->insertGetId(['name' => 'Acme', 'slug' => 'acme', 'is_enabled' => true]);
    DB::table('brand_translations')->insert(['brand_id' => $id, 'locale' => 'en', 'name' => 'Acme Inc', 'slug' => 'acme-inc', 'created_at' => now(), 'updated_at' => now()]);

    config()->set('app-features.features.brand', true);
    $this->get('/en/brands/acme')->assertRedirect('/en/brands/acme-inc');
});
