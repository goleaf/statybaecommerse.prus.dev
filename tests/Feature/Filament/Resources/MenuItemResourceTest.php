<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel is registered for every test execution.
        $this->resolveAdminPanel();

        // Stabilise translations and date formatting for assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Provision and authenticate a reusable administrator account.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_menu_items(): void
    {
        $menu = Menu::factory()->create(['name' => 'Primary']);
        $items = MenuItem::factory()->count(2)->create([
            'menu_id' => $menu->id,
            'is_visible' => true,
        ]);

        Livewire::test(ListMenuItems::class)
            // Hydrate the table so we can assert on the rendered records.
            ->call('loadTable')
            ->assertCanSeeTableRecords($items);
    }

    public function test_can_create_menu_item_via_form(): void
    {
        $menu = Menu::factory()->create();

        Livewire::test(CreateMenuItem::class)
            // Populate the form with both relational and standalone attributes.
            ->fillForm([
                'menu_id'    => $menu->id,
                'label'      => 'About Us',
                'url'        => '/about',
                'route_name' => 'about',
                'icon'       => 'heroicon-o-information-circle',
                'sort_order' => 5,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_items', [
            'menu_id'    => $menu->id,
            'label'      => 'About Us',
            'url'        => '/about',
            'route_name' => 'about',
            'icon'       => 'heroicon-o-information-circle',
            'sort_order' => 5,
            'is_visible' => true,
        ]);
    }

    public function test_can_edit_menu_item_via_form(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id'    => $menu->id,
            'label'      => 'Contact',
            'url'        => '/contact',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        Livewire::test(EditMenuItem::class, ['record' => $menuItem->getRouteKey()])
            // Flip both the target route and the visibility toggle to mirror real workflows.
            ->fillForm([
                'label'      => 'Support',
                'url'        => '/support',
                'route_name' => 'support',
                'sort_order' => 2,
                'is_visible' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_items', [
            'id'         => $menuItem->id,
            'label'      => 'Support',
            'url'        => '/support',
            'route_name' => 'support',
            'sort_order' => 2,
            'is_visible' => false,
        ]);
    }

    public function test_table_delete_action_removes_record(): void
    {
        $menuItem = MenuItem::factory()->create();

        Livewire::test(ListMenuItems::class)
            ->call('loadTable')
            // Trigger the inline delete action and assert Filament reports a clean run.
            ->callTableAction('delete', $menuItem)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('menu_items', [
            'id' => $menuItem->id,
        ]);
    }

    public function test_menu_filter_limits_results_to_selected_menu(): void
    {
        $menuA = Menu::factory()->create(['name' => 'Header']);
        $menuB = Menu::factory()->create(['name' => 'Footer']);

        $headerItem = MenuItem::factory()->create([
            'menu_id' => $menuA->id,
            'label'   => 'Home',
        ]);

        $footerItem = MenuItem::factory()->create([
            'menu_id' => $menuB->id,
            'label'   => 'Contact',
        ]);

        Livewire::test(ListMenuItems::class)
            ->call('loadTable')
            // Restrict the results to the header menu and confirm the footer entry disappears.
            ->filterTable('menu_id', $menuA->id)
            ->assertCanSeeTableRecords([$headerItem])
            ->assertCanNotSeeTableRecords([$footerItem]);
    }

    public function test_visibility_filter_handles_hidden_items(): void
    {
        $visibleItem = MenuItem::factory()->create(['is_visible' => true]);
        $hiddenItem = MenuItem::factory()->create(['is_visible' => false]);

        Livewire::test(ListMenuItems::class)
            ->call('loadTable')
            // Filter for only hidden items using the ternary filter state.
            ->filterTable('is_visible', 'false')
            ->assertCanSeeTableRecords([$hiddenItem])
            ->assertCanNotSeeTableRecords([$visibleItem]);
    }

    public function test_bulk_delete_action_removes_selected_items(): void
    {
        $items = MenuItem::factory()->count(2)->create();

        Livewire::test(ListMenuItems::class)
            ->call('loadTable')
            // Execute the delete bulk action shipped with the resource toolbar.
            ->callTableBulkAction('delete', $items)
            ->assertHasNoTableBulkActionErrors();

        foreach ($items as $item) {
            $this->assertDatabaseMissing('menu_items', [
                'id' => $item->id,
            ]);
        }
    }
}
