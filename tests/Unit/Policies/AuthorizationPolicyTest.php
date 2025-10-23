<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function policyUser(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role, 'web');
    $user->assignRole($role);

    return $user;
}

it('grants admins full catalog access', function (): void {
    $admin = policyUser('admin');
    $product = Product::factory()->make();
    $category = Category::factory()->make();
    $brand = Brand::factory()->make();

    $productPolicy = app(ProductPolicy::class);
    $categoryPolicy = app(CategoryPolicy::class);
    $brandPolicy = app(BrandPolicy::class);

    expect($productPolicy->create($admin))->toBeTrue()
        ->and($productPolicy->update($admin, $product))->toBeTrue()
        ->and($productPolicy->delete($admin, $product))->toBeTrue();

    expect($categoryPolicy->create($admin))->toBeTrue()
        ->and($brandPolicy->update($admin, $brand))->toBeTrue();
});

it('limits managers to non-destructive catalog changes', function (): void {
    $manager = policyUser('manager');
    $product = Product::factory()->make();
    $order = Order::factory()->make();

    $productPolicy = app(ProductPolicy::class);
    $orderPolicy = app(OrderPolicy::class);

    expect($productPolicy->create($manager))->toBeTrue()
        ->and($productPolicy->update($manager, $product))->toBeTrue()
        ->and($productPolicy->delete($manager, $product))->toBeFalse();

    expect($orderPolicy->update($manager, $order))->toBeTrue()
        ->and($orderPolicy->delete($manager, $order))->toBeFalse();
});

it('restricts editors to updates only', function (): void {
    $editor = policyUser('editor');
    $product = Product::factory()->make();

    $productPolicy = app(ProductPolicy::class);

    expect($productPolicy->create($editor))->toBeFalse()
        ->and($productPolicy->update($editor, $product))->toBeTrue()
        ->and($productPolicy->delete($editor, $product))->toBeFalse();
});

it('allows viewers to read but not modify data', function (): void {
    $viewer = policyUser('viewer');
    $product = Product::factory()->make();
    $order = Order::factory()->make();
    $userPolicy = app(UserPolicy::class);

    expect(app(ProductPolicy::class)->view($viewer, $product))->toBeTrue()
        ->and(app(ProductPolicy::class)->update($viewer, $product))->toBeFalse();

    expect(app(OrderPolicy::class)->view($viewer, $order))->toBeTrue()
        ->and(app(OrderPolicy::class)->update($viewer, $order))->toBeFalse();

    expect($userPolicy->viewAny($viewer))->toBeFalse();
});
