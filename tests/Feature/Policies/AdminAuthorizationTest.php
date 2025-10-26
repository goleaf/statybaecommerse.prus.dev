<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Models\AdminUser;
use App\Support\Authorization\AuthorizationMatrix;
use Database\Seeders\AdminAuthorizationSeeder;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        config(['filament.auth.guard' => 'admin']);
        Filament::setCurrentPanel('admin');
    }

    public function test_admin_role_can_access_panel(): void
    {
        $user = AdminUser::factory()->create();
        $user->assignRole('admin');

        $panel = Mockery::mock(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_authorization_matrix_uses_panel_guard_when_config_missing(): void
    {
        config(['filament.auth.guard' => null]);
        Filament::setCurrentPanel('admin');

        $user = AdminUser::factory()->create();
        $user->assignRole('admin');

        // Act as the admin guard explicitly to mirror the production setup
        // where Filament registers its own guard for admin requests.
        $this->actingAs($user, 'admin');

        $this->assertTrue(AuthorizationMatrix::check('products', 'viewAny'));
    }

    public function test_support_role_cannot_open_product_create_page(): void
    {
        $user = AdminUser::factory()->create();
        $user->assignRole('support');

        $this->actingAs($user, 'admin')
            ->get(ProductResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_support_role_can_view_orders_index(): void
    {
        $user = AdminUser::factory()->create();
        $user->assignRole('support');

        $this->actingAs($user, 'admin')
            ->get(OrderResource::getUrl('index'))
            ->assertOk();
    }

    public function test_admin_role_can_open_product_create_page(): void
    {
        $user = AdminUser::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user, 'admin')
            ->get(ProductResource::getUrl('create'))
            ->assertOk();
    }
}
