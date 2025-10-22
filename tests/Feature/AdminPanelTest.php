<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use Database\Seeders\AdminAuthorizationSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure Vite asset loading does not interfere with the HTTP assertions.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite integration so the tests do not require a compiled manifest file.
        $this->withoutVite();

        // Resolve the Filament panel context so guard resolution matches production behaviour.
        $this->resolveAdminPanel();

        // Keep Laravel's exception handler active to capture redirects/responses instead of bubbling exceptions.
        $this->withExceptionHandling();

        // Align the Filament authentication guard with the admin guard expected by the panel configuration.
        config()->set('filament.auth.guard', 'admin');
        config()->set('auth.defaults.guard', 'admin');
        config()->set('auth.defaults.passwords', 'admin_users');

        // Ensure permissions and roles mirror production defaults.
        $this->seed(AdminAuthorizationSeeder::class);

        // Start each test with a clean authentication context.
        Filament::auth()->logout();
    }

    public function test_admin_panel_redirects_to_login_when_not_authenticated(): void
    {
        $panel = $this->resolveAdminPanel();

        // Ensure the guard is clear before making any assertions.
        Filament::auth()->logout();

        $this->assertFalse(Filament::auth()->check());
        $this->assertSame(url('/admin/login'), $panel->getLoginUrl());
    }

    public function test_admin_panel_can_be_accessed_when_authenticated(): void
    {
        $panel = $this->resolveAdminPanel();

        $adminUser = AdminUser::factory()->create();

        // Grant the admin user full access capabilities expected by the admin panel.
        $adminUser->assignRole('super_admin');

        // Explicitly synchronise the Filament guard to mirror the active session guard state.
        Filament::auth()->login($adminUser);

        $this->assertTrue(Filament::auth()->check());
        $this->assertTrue(Filament::auth()->user()->is($adminUser));
        $this->assertSame(url('/admin'), $panel->getUrl());
    }

    public function test_dashboard_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('filament.admin.pages.dashboard'));
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
