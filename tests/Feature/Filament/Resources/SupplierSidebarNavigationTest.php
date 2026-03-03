<?php

declare(strict_types=1);

use App\Enums\NavigationGroup;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    config()->set('authorization.testing.skip_checks', false);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::findOrCreate('view_suppliers', 'admin');
});

afterEach(function (): void {
    config()->set('authorization.testing.skip_checks', true);
});

it('registers supplier sidebar navigation for admins with view permission', function (): void {
    $admin = AdminUser::factory()->create();
    $admin->givePermissionTo('view_suppliers');

    $this->actingAs($admin, 'admin');

    expect(SupplierResource::canViewAny())->toBeTrue()
        ->and(SupplierResource::shouldRegisterNavigation())->toBeTrue();

    $navigationItems = SupplierResource::getNavigationItems();

    expect($navigationItems)->toHaveCount(1)
        ->and($navigationItems[0]->getLabel())->toBe(__('admin.suppliers.navigation_label'))
        ->and($navigationItems[0]->getGroup())->toBe(NavigationGroup::Products->label())
        ->and($navigationItems[0]->getUrl())->toBe(SupplierResource::getUrl('index'));

    $this->get(SupplierResource::getUrl('index'))->assertOk();
});

it('hides supplier sidebar navigation and denies index when permission is missing', function (): void {
    $admin = AdminUser::factory()->create();

    $this->actingAs($admin, 'admin');

    expect(SupplierResource::canViewAny())->toBeFalse()
        ->and(SupplierResource::shouldRegisterNavigation())->toBeFalse()
        ->and(SupplierResource::getNavigationItems())->toBe([]);

    $this->get(SupplierResource::getUrl('index'))->assertForbidden();
});
