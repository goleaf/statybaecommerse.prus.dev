<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AddressPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductRequestPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use App\Support\Authorization\AuthorizationMatrix;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function policyUser(string $role): User
{
    $user = User::factory()->make([
        'id' => random_int(1, PHP_INT_MAX),
    ]);

    $roleModel = \Spatie\Permission\Models\Role::make([
        'name'       => $role,
        'guard_name' => 'web',
    ]);

    $user->setRelation('roles', collect([$roleModel]));

    return $user;
}

it('unit: grants admins full catalog access', function (): void {
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

it('unit: limits managers to non-destructive catalog changes', function (): void {
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

it('unit: restricts editors to updates only', function (): void {
    $editor = policyUser('editor');
    $product = Product::factory()->make();

    $productPolicy = app(ProductPolicy::class);

    expect($productPolicy->create($editor))->toBeFalse()
        ->and($productPolicy->update($editor, $product))->toBeTrue()
        ->and($productPolicy->delete($editor, $product))->toBeFalse();
});

it('unit: allows viewers to read but not modify data', function (): void {
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

it('unit: storefront users can manage their own addresses without global permissions', function (): void {
    $addressOwner = policyUser('user');
    $address = Address::factory()->make([
        'user_id' => $addressOwner->getKey(),
    ]);

    $addressPolicy = app(AddressPolicy::class);

    expect($addressPolicy->view($addressOwner, $address))->toBeTrue()
        ->and($addressPolicy->update($addressOwner, $address))->toBeTrue();

    $otherUser = policyUser('user');

    expect($addressPolicy->update($otherUser, $address))->toBeFalse();
});

it('unit: matrix grants admin settings access while blocking viewers', function (): void {
    $admin = policyUser('admin');
    $viewer = policyUser('viewer');
    $setting = SystemSetting::factory()->make();

    $policy = app(SystemSettingPolicy::class);

    expect($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $setting))->toBeTrue();

    expect($policy->viewAny($viewer))->toBeFalse();
});

it('unit: notification policies honour both matrix permissions and ownership rules', function (): void {
    $admin = policyUser('admin');
    $recipient = policyUser('user');
    $viewer = policyUser('viewer');

    $notification = Notification::factory()->make([
        'notifiable_type' => User::class,
        'notifiable_id'   => $recipient->getKey(),
    ]);

    $policy = app(NotificationPolicy::class);

    expect($policy->viewAny($recipient))->toBeTrue()
        ->and($policy->markAsRead($recipient, $notification))->toBeTrue();

    expect($policy->markAsRead($viewer, $notification))->toBeFalse();

    expect($policy->markAsUnread($admin, $notification))->toBeTrue();
});

it('unit: product request policies combine matrix checks with ownership fallbacks', function (): void {
    $admin = policyUser('admin');
    $owner = policyUser('user');
    $request = ProductRequest::factory()->make([
        'user_id' => $owner->getKey(),
    ]);

    $policy = app(ProductRequestPolicy::class);

    expect($policy->view($owner, $request))->toBeTrue()
        ->and($policy->update($owner, $request))->toBeTrue();

    expect($policy->respond($admin, $request))->toBeTrue();
});

it('unit: role policy honours matrix driven maintenance abilities', function (): void {
    // Create representative users with elevated and read-only permissions.
    $admin = policyUser('admin');
    $viewer = policyUser('viewer');

    // Build an in-memory role model to satisfy policy signatures.
    $role = \Spatie\Permission\Models\Role::make([
        'name'       => 'sample-role',
        'guard_name' => 'web',
    ]);

    $policy = app(RolePolicy::class);

    expect($policy->restore($admin, $role))->toBeTrue()
        ->and($policy->forceDelete($admin, $role))->toBeTrue()
        ->and($policy->forceDeleteAny($admin))->toBeTrue()
        ->and($policy->restoreAny($admin))->toBeTrue()
        ->and($policy->replicate($admin, $role))->toBeTrue()
        ->and($policy->reorder($admin))->toBeTrue();

    expect($policy->restore($viewer, $role))->toBeFalse()
        ->and($policy->forceDelete($viewer, $role))->toBeFalse()
        ->and($policy->replicate($viewer, $role))->toBeFalse();
});

it('unit: authorization matrix maps advanced role actions to canonical permissions', function (): void {
    // Confirm each advanced ability references an existing permission string.
    expect(AuthorizationMatrix::ability('roles', 'restore'))->toBe('roles.update')
        ->and(AuthorizationMatrix::ability('roles', 'forceDelete'))->toBe('roles.delete')
        ->and(AuthorizationMatrix::ability('roles', 'forceDeleteAny'))->toBe('roles.delete')
        ->and(AuthorizationMatrix::ability('roles', 'restoreAny'))->toBe('roles.update')
        ->and(AuthorizationMatrix::ability('roles', 'replicate'))->toBe('roles.create')
        ->and(AuthorizationMatrix::ability('roles', 'reorder'))->toBe('roles.update');
});
