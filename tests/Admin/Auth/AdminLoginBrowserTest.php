<?php

declare(strict_types=1);

namespace Tests\Admin\Auth;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

final class AdminLoginBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function admin_can_login_through_filament_panel(): void
    {
        // Provision the canonical admin account with the known password so the browser flow mirrors production credentials.
        $admin = AdminUser::factory()
            ->withPassword('admin123')
            ->create([
                'email' => 'admin@example.com',
                'name' => 'Administrator',
            ]);

        // Drive the Filament login form and confirm the dashboard becomes available after authentication.
        $this->browse(function (Browser $browser) use ($admin): void {
            $browser->visit('/admin/login')
                ->waitFor('input[name="email"]')
                ->type('email', $admin->email)
                ->type('password', 'admin123')
                ->press('Prisijungti')
                ->waitForLocation('/admin')
                ->assertPathIs('/admin')
                ->waitForText('Valdymo skydas')
                ->assertSee('Valdymo skydas');
        });
    }
}

