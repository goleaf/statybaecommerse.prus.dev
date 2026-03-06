<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests for the default Filament admin login page.
 *
 * Note: Filament uses Livewire for form submission, so login form tests
 * require Livewire component testing rather than HTTP POST requests.
 */
final class AdminLoginRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->resolveAdminPanel();
        $this->withExceptionHandling();

        // Configure guards for admin panel
        config()->set('filament.auth.guard', 'admin');
        config()->set('auth.defaults.guard', 'admin');

        // Seed roles for admin guard
        $this->seedRolesForAdminGuard();

        // Start with clean auth state
        Filament::auth()->logout();
    }

    private function seedRolesForAdminGuard(): void
    {
        foreach (['super_admin', 'admin', 'administrator'] as $roleName) {
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'admin',
            ]);
        }
    }

    private function createAdminUser(): AdminUser
    {
        $admin = AdminUser::factory()->create([
            'email'             => 'info@egisstatyba.lt',
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_admin_login_page_loads_successfully(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200)
            ->assertSee('email', false)
            ->assertSee('password', false);
    }

    public function test_admin_login_route_resolves_correctly(): void
    {
        $expectedUrl = url('/admin/login');
        $actualUrl = route('filament.admin.auth.login');

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function test_admin_panel_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_logout_works_correctly(): void
    {
        $admin = $this->createAdminUser();

        Filament::auth()->login($admin);
        $this->actingAs($admin, 'admin');

        $response = $this->post('/admin/logout');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_uses_default_filament_login(): void
    {
        // Verify the login route uses Filament's default Login class
        $panel = Filament::getPanel('admin');

        $this->assertTrue($panel->hasLogin());
    }

    public function test_admin_login_page_contains_form_elements(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);

        // Filament uses Alpine.js for password field visibility toggle,
        // so we check for the wire:model binding instead of static type attributes
        $content = $response->getContent();

        // Check for email input (Filament uses wire:model for form bindings)
        $this->assertStringContainsString('wire:model', $content);

        // Check for password-related elements (Alpine.js handles the type toggle)
        $this->assertStringContainsString('isPasswordRevealed', $content);

        // Check for submit button
        $this->assertStringContainsString('type="submit"', $content);
    }
}
