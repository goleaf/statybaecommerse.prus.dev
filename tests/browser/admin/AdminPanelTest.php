<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminPanelTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_login_to_panel(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->assertSee('Sign in to your account')
                ->type('email', $this->admin->email)
                ->type('password', 'password')
                ->press('Sign in')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin')
                ->assertSee('Dashboard');
        });
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->visit('/admin/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Sign in')
                ->waitFor('.fi-fo-field-wrp-error-message')
                ->assertSee('These credentials do not match our records');
        });
    }

    public function test_admin_dashboard_displays_widgets(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->assertSee('Dashboard')
                ->assertPresent('[data-widget="stats-overview"]')
                ->assertPresent('[data-widget="sales-chart"]')
                ->assertPresent('[data-widget="recent-orders"]');
        });
    }

    public function test_admin_can_navigate_to_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->clickLink('Products')
                ->waitForLocation('/admin/products')
                ->assertPathIs('/admin/products')
                ->assertSee('Products');
        });
    }

    public function test_admin_can_navigate_to_orders(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->clickLink('Orders')
                ->waitForLocation('/admin/orders')
                ->assertPathIs('/admin/orders')
                ->assertSee('Orders');
        });
    }

    public function test_admin_can_navigate_to_customers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->clickLink('Users')
                ->waitForLocation('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('Users');
        });
    }

    public function test_admin_can_access_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->clickLink('Settings')
                ->waitForLocation('/admin/system-settings')
                ->assertPathIs('/admin/system-settings')
                ->assertSee('System Settings');
        });
    }

    public function test_admin_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->click('[data-user-menu-trigger]')
                ->waitFor('[data-user-menu]')
                ->clickLink('Sign out')
                ->waitForLocation('/admin/login')
                ->assertPathIs('/admin/login')
                ->assertSee('Sign in to your account');
        });
    }

    public function test_admin_panel_is_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            // Test desktop view
            $browser
                ->resize(1200, 800)
                ->loginAs($this->admin)
                ->visit('/admin')
                ->assertPresent('.fi-sidebar')
                ->assertVisible('.fi-sidebar');

            // Test mobile view
            $browser
                ->resize(375, 667)
                ->refresh()
                ->assertPresent('.fi-sidebar')
                ->assertNotVisible('.fi-sidebar');
        });
    }

    public function test_admin_can_use_global_search(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->keys('[data-global-search-input]', 'test')
                ->waitFor('[data-global-search-results]')
                ->assertPresent('[data-global-search-results]');
        });
    }

    public function test_admin_panel_dark_mode_toggle(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->click('[data-theme-toggle]')
                ->pause(500)
                ->assertAttribute('html', 'class', 'dark')
                ->click('[data-theme-toggle]')
                ->pause(500)
                ->assertAttributeDoesntContain('html', 'class', 'dark');
        });
    }

    public function test_admin_panel_notifications(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->admin)
                ->visit('/admin')
                ->assertPresent('[data-notifications-trigger]')
                ->click('[data-notifications-trigger]')
                ->waitFor('[data-notifications-panel]')
                ->assertPresent('[data-notifications-panel]');
        });
    }
}
