<?php declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');

    Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => is_string($guard) ? $guard : 'web',
    ]);

    $this->adminUser = User::factory()->create(['email' => 'admin@example.com']);
    $this->adminUser->syncRoles(['admin']);
    $this->actingAs($this->adminUser);
});

it('can render product index page', function () {
    $this
        ->get(ProductResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list products', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can render create page', function () {
    $this
        ->get(ProductResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render view page', function () {
    $product = Product::factory()->create();

    $this
        ->get(ProductResource::getUrl('view', ['record' => $product]))
        ->assertSuccessful();
});

it('can render edit page', function () {
    $product = Product::factory()->create();

    $this
        ->get(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertSuccessful();
});
