<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages\CreateMenu;
use App\Filament\Resources\MenuResource\Pages\EditMenu;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel so Livewire table helpers behave correctly.
        $this->resolveAdminPanel();

        // Keep translations deterministic during assertions by forcing the English locale.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create a reusable admin user and authenticate once for all tests.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_menu_records(): void
    {
        // Seed two menus so we can ensure the listing renders multiple records.
        $menus = Menu::factory()->count(2)->create();

        Livewire::test(ListMenus::class)
            // Hydrate the table data before making assertions as recommended by our AGENT notes.
            ->call('loadTable')
            ->assertCanSeeTableRecords($menus);
    }

    public function test_can_create_menu_via_filament_form(): void
    {
        Livewire::test(CreateMenu::class)
            // Provide a full payload that exercises validation and helper text wiring.
            ->fillForm([
                'name'        => 'Navigation',
                'key'         => 'navigation',
                'location'    => 'header',
                'description' => 'Primary navigation menu',
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Confirm persistence captured both the key and the active toggle.
        $this->assertDatabaseHas('menus', [
            'name'      => 'Navigation',
            'key'       => 'navigation',
            'location'  => 'header',
            'is_active' => true,
        ]);
    }

    public function test_can_edit_menu_via_filament_form(): void
    {
        $menu = Menu::factory()->create([
            'name'        => 'Footer Links',
            'description' => 'Original description',
            'location'    => 'footer',
        ]);

        Livewire::test(EditMenu::class, ['record' => $menu->getRouteKey()])
            // Update both the copy and the active toggle to mirror common admin actions.
            ->fillForm([
                'name'        => 'Updated Footer Links',
                'description' => 'Updated copy for the footer menu',
                'is_active'   => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menus', [
            'id'          => $menu->id,
            'name'        => 'Updated Footer Links',
            'description' => 'Updated copy for the footer menu',
            'is_active'   => false,
        ]);
    }

    public function test_toggle_active_table_action_updates_menu(): void
    {
        $menu = Menu::factory()->create(['is_active' => false]);

        Livewire::test(ListMenus::class)
            ->call('loadTable')
            // Invoke the toggle action and ensure Filament reports success.
            ->callTableAction('toggle_active', $menu)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('menus', [
            'id'        => $menu->id,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_action_creates_timestamped_copy(): void
    {
        // Freeze time so the generated key suffix remains deterministic across runs.
        Carbon::setTestNow('2025-01-01 00:00:00');

        $menu = Menu::factory()->create([
            'name' => 'Main Menu',
            'key'  => 'main-menu',
        ]);

        try {
            Livewire::test(ListMenus::class)
                ->call('loadTable')
                ->callTableAction('duplicate', $menu)
                ->assertHasNoTableActionErrors();
        } finally {
            // Always clear the fake clock so subsequent tests observe real timestamps.
            Carbon::setTestNow();
        }

        $this->assertDatabaseHas('menus', [
            'name' => 'Main Menu (Copy)',
            'key'  => 'main-menu_copy_1735689600',
        ]);
    }

    public function test_location_filter_limits_table_results(): void
    {
        $headerMenu = Menu::factory()->create(['location' => 'header']);
        $footerMenu = Menu::factory()->create(['location' => 'footer']);

        Livewire::test(ListMenus::class)
            ->call('loadTable')
            // Apply the select filter to only surface header menus.
            ->filterTable('location', ['header'])
            ->assertCanSeeTableRecords([$headerMenu])
            ->assertCanNotSeeTableRecords([$footerMenu]);
    }

    public function test_bulk_activation_and_deactivation_actions(): void
    {
        $menus = Menu::factory()->count(2)->create(['is_active' => false]);

        Livewire::test(ListMenus::class)
            ->call('loadTable')
            // Activate every menu in one shot using the bulk helper.
            ->callTableBulkAction('activate', $menus)
            ->assertHasNoTableBulkActionErrors();

        foreach ($menus as $menu) {
            $this->assertDatabaseHas('menus', [
                'id'        => $menu->id,
                'is_active' => true,
            ]);
        }

        Livewire::test(ListMenus::class)
            ->call('loadTable')
            // Immediately deactivate the same records to exercise the complementary bulk action.
            ->callTableBulkAction('deactivate', $menus)
            ->assertHasNoTableBulkActionErrors();

        foreach ($menus as $menu) {
            $this->assertDatabaseHas('menus', [
                'id'        => $menu->id,
                'is_active' => false,
            ]);
        }
    }
}
