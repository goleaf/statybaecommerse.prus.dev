<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_panel_can_be_accessed_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_route_exists(): void
    {
        $this->assertTrue(route_exists('filament.admin.pages.dashboard'));
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
