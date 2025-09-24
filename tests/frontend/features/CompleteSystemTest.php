<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Product;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\MultiLanguageTabService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('complete system integration test', function () {
    // Create admin user with permissions
    $admin = User::factory()->create([
        'name' => 'System Admin',
        'email' => 'admin@system.test',
    ]);

    $permissions = ['view_admin_panel', 'view_any_product', 'create_product', 'view_any_document_template'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions($permissions);
    $admin->assignRole($adminRole);

    // Test brand creation
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
        'is_enabled' => true,
    ]);

    // Test category creation
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_visible' => true,
    ]);

    // Test product creation
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'brand_id' => $brand->id,
        'price' => 99.99,
        'is_visible' => true,
    ]);

    // Test product-category relationship
    $product->categories()->attach($category->id);

    // Test document template creation
    $template = DocumentTemplate::factory()->create([
        'name' => 'Product Invoice',
        'content' => '<h1>Invoice for $PRODUCT_NAME</h1><p>Price: $PRODUCT_PRICE</p>',
        'variables' => ['$PRODUCT_NAME', '$PRODUCT_PRICE'],
        'type' => 'invoice',
        'is_active' => true,
    ]);

    // Test document generation
    $this->actingAs($admin);
    $service = app(DocumentService::class);
    $document = $service->generateDocument(
        $template,
        $product,
        ['$PRODUCT_NAME' => $product->name, '$PRODUCT_PRICE' => '€'.$product->price],
        'Product Invoice #001'
    );

    // Assertions
    expect($brand->products)->toHaveCount(1);
    expect($category->products)->toHaveCount(1);
    expect($product->brand->name)->toBe('Test Brand');
    expect($product->categories->first()->name)->toBe('Test Category');
    expect($document->title)->toBe('Product Invoice #001');
    expect($document->content)->toContain('Test Product');
    expect($document->content)->toContain('€99.99');
    expect($document->documentable)->toBeInstanceOf(Product::class);
    expect($document->template)->toBeInstanceOf(DocumentTemplate::class);
});

it('translation system integration test', function () {
    $languages = MultiLanguageTabService::getAvailableLanguages();

    expect($languages)->toBeArray();
    expect($languages)->not()->toBeEmpty();

    foreach ($languages as $language) {
        expect($language)->toHaveKeys(['code', 'name', 'flag']);
        expect(MultiLanguageTabService::getLanguageName($language['code']))->toBeString();
        expect(MultiLanguageTabService::getLanguageFlag($language['code']))->toBeString();
    }
});

it('models have proper relationships', function () {
    $product = Product::factory()->create();
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();

    expect($product->brand())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($product->categories())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    expect($product->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($brand->products())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($category->products())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

it('factories create valid models', function () {
    $product = Product::factory()->create();
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $user = User::factory()->create();
    $template = DocumentTemplate::factory()->create();
    $document = Document::factory()->create();

    expect($product)->toBeInstanceOf(Product::class);
    expect($brand)->toBeInstanceOf(Brand::class);
    expect($category)->toBeInstanceOf(Category::class);
    expect($user)->toBeInstanceOf(User::class);
    expect($template)->toBeInstanceOf(DocumentTemplate::class);
    expect($document)->toBeInstanceOf(Document::class);

    expect($user->name)->not()->toBeEmpty();
    expect($user->email)->toContain('@');
    expect($product->price)->toBeGreaterThan(0);
    expect($template->is_active)->toBeTrue();
});
