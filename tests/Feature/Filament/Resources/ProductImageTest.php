<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);
    $this->brand = Brand::factory()->create();

    // Create default currency to avoid current_currency_model() returning null
    \App\Models\Currency::factory()->create([
        'code'       => 'EUR',
        'is_default' => true,
        'is_active'  => true,
        'is_enabled' => true,
    ]);

    if (! Schema::hasTable('suppliers')) {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company_code');
            $table->string('code')->unique();
            $table->string('vat_code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    if (! Schema::hasTable('product_supplier')) {
        Schema::create('product_supplier', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'supplier_id']);
        });
    }

    if (Schema::hasTable('products')) {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable();
            }
            if (! Schema::hasColumn('products', 'detailed_description')) {
                $table->text('detailed_description')->nullable();
            }
            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('products', 'allow_backorder')) {
                $table->boolean('allow_backorder')->default(false);
            }
            if (! Schema::hasColumn('products', 'size')) {
                $table->string('size')->nullable();
            }
            if (! Schema::hasColumn('products', 'size_type')) {
                $table->string('size_type')->nullable();
            }
            if (! Schema::hasColumn('products', 'color')) {
                $table->string('color')->nullable();
            }
            if (! Schema::hasColumn('products', 'pack_size')) {
                $table->string('pack_size')->nullable();
            }
            if (! Schema::hasColumn('products', 'pack_size_type')) {
                $table->string('pack_size_type')->nullable();
            }
            if (! Schema::hasColumn('products', 'is_venipak_locker_excluded')) {
                $table->boolean('is_venipak_locker_excluded')->default(false);
            }
            if (! Schema::hasColumn('products', 'is_venipak_courier_excluded')) {
                $table->boolean('is_venipak_courier_excluded')->default(false);
            }
        });
    }

    if (Schema::hasTable('product_supplier')) {
        DB::table('product_supplier')->delete();
    }

    if (Schema::hasTable('suppliers')) {
        DB::table('suppliers')->delete();
    }

    Storage::fake('public');
});

it('creates product successfully without inline image repeater', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name'     => 'Product Save Test',
            'slug'     => 'product-save-test',
            'sku'      => 'SAVE-001',
            'price'    => 100,
            'status'   => 'draft',
            'brand_id' => $this->brand->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::withoutGlobalScopes()->where('sku', 'SAVE-001')->first();
    expect($product)->not->toBeNull();
    expect($product?->images()->withoutGlobalScopes()->count())->toBe(0);
});

it('keeps uploaded product images on disk when creating through images relation manager', function () {
    $product = Product::factory()->create([
        'name'         => 'Editable Product',
        'slug'         => 'editable-product',
        'sku'          => 'EDIT-001',
        'price'        => 120,
        'status'       => 'published',
        'published_at' => now(),
        'brand_id'     => $this->brand->id,
    ]);

    Storage::disk('public')->put('product-images/edit-product.jpg', 'test-content');

    Livewire::test(ImagesRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->mountTableAction('create')
        ->set('mountedActions.0.data.path', ['product-images/edit-product.jpg'])
        ->set('mountedActions.0.data.is_default', true)
        ->set('mountedActions.0.data.is_active', true)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $image = $product->images()->withoutGlobalScopes()->latest('id')->first();
    expect($image)->not->toBeNull();

    Storage::disk('public')->assertExists($image?->path ?? '');
});
