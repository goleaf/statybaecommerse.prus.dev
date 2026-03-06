<?php

declare(strict_types=1);

use App\Filament\Resources\Brochures\BrochureResource;
use App\Models\AdminUser;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('authorization.testing.skip_checks', false);
    config()->set('filament.auth.guard', 'admin');
    config()->set('auth.defaults.guard', 'admin');
    config()->set('auth.defaults.passwords', 'admin_users');

    $this->withoutVite();
    $this->resolveAdminPanel();
    $this->seed(AdminAuthorizationSeeder::class);
});

it('shows brochures navigation and allows index for seeded super admin', function (): void {
    $this->seed(AdminUserSeeder::class);

    $admin = AdminUser::query()
        ->where('email', 'info@egisstatyba.lt')
        ->firstOrFail();

    $this->actingAs($admin, 'admin');

    expect(BrochureResource::canViewAny())->toBeTrue();
    $this->get(BrochureResource::getUrl('index'))
        ->assertOk()
        ->assertSee(__('admin.brochures.navigation_label'));
});

it('hides brochures navigation and forbids index for admin without brochure permissions', function (): void {
    $admin = AdminUser::factory()->create();

    $this->actingAs($admin, 'admin');

    expect(BrochureResource::canViewAny())->toBeFalse();

    $this->get(BrochureResource::getUrl('index'))->assertForbidden();
});
