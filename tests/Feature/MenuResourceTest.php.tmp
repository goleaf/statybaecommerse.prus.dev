<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

final class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_can_list_menus(): void
    {
        // Arrange
        $menus = Menu::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->assertCanSeeTableRecords($menus);
    }

    public function test_can_create_menu(): void
    {
        // Arrange
        $menuData = [
            'name' => 'Test Menu',
            'key' => 'test_menu',
            'location' => 'header',
            'description' => 'Test menu description',
            'is_active' => true,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\CreateMenu::class)
            ->fillForm($menuData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('menus', [
            'name' => 'Test Menu',
            'key' => 'test_menu',
            'location' => 'header',
            'is_active' => true,
        ]);
    }

    public function test_can_edit_menu(): void
    {
        // Arrange
        $menu = Menu::factory()->create([
            'name' => 'Original Menu',
            'key' => 'original_menu',
        ]);

        $updatedData = [
            'name' => 'Updated Menu',
            'description' => 'Updated description',
        ];

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\EditMenu::class, [
            'record' => $menu->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Updated Menu',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_menu(): void
    {
        // Arrange
        $menu = Menu::factory()->create();

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->callTableAction('delete', $menu);

        // Assert
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_can_toggle_menu_active_status(): void
    {
        // Arrange
        $menu = Menu::factory()->create(['is_active' => false]);

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->callTableAction('toggle_active', $menu);

        // Assert
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'is_active' => true,
        ]);
    }

    public function test_can_duplicate_menu(): void
    {
        // Arrange
        $menu = Menu::factory()->create([
            'name' => 'Original Menu',
            'key' => 'original_menu',
        ]);

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->callTableAction('duplicate', $menu);

        // Assert
        $this->assertDatabaseHas('menus', [
            'name' => 'Original Menu (Copy)',
        ]);

        $this->assertDatabaseHas('menus', [
            'key' => 'original_menu_copy_'.time(),
        ]);
    }

    public function test_can_filter_menus_by_location(): void
    {
        // Arrange
        $headerMenu = Menu::factory()->create(['location' => 'header']);
        $footerMenu = Menu::factory()->create(['location' => 'footer']);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->filterTable('location', ['header'])
            ->assertCanSeeTableRecords([$headerMenu])
            ->assertCanNotSeeTableRecords([$footerMenu]);
    }

    public function test_can_filter_menus_by_active_status(): void
    {
        // Arrange
        $activeMenu = Menu::factory()->create(['is_active' => true]);
        $inactiveMenu = Menu::factory()->create(['is_active' => false]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->filterTable('is_active', 'true')
            ->assertCanSeeTableRecords([$activeMenu])
            ->assertCanNotSeeTableRecords([$inactiveMenu]);
    }

    public function test_can_bulk_activate_menus(): void
    {
        // Arrange
        $menus = Menu::factory()->count(3)->create(['is_active' => false]);

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->callTableBulkAction('activate', $menus);

        // Assert
        foreach ($menus as $menu) {
            $this->assertDatabaseHas('menus', [
                'id' => $menu->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_menus(): void
    {
        // Arrange
        $menus = Menu::factory()->count(3)->create(['is_active' => true]);

        // Act
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->callTableBulkAction('deactivate', $menus);

        // Assert
        foreach ($menus as $menu) {
            $this->assertDatabaseHas('menus', [
                'id' => $menu->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_menu_validation_requires_name(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'key' => 'test_menu',
                'location' => 'header',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_menu_validation_requires_key(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'name' => 'Test Menu',
                'location' => 'header',
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_menu_validation_requires_location(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'name' => 'Test Menu',
                'key' => 'test_menu',
            ])
            ->call('create')
            ->assertHasFormErrors(['location']);
    }

    public function test_menu_key_must_be_unique(): void
    {
        // Arrange
        $existingMenu = Menu::factory()->create(['key' => 'existing_key']);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\CreateMenu::class)
            ->fillForm([
                'name' => 'Test Menu',
                'key' => 'existing_key',
                'location' => 'header',
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_can_view_menu_details(): void
    {
        // Arrange
        $menu = Menu::factory()->create([
            'name' => 'Test Menu',
            'key' => 'test_menu',
            'location' => 'header',
            'description' => 'Test description',
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ViewMenu::class, [
            'record' => $menu->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => 'Test Menu',
                'key' => 'test_menu',
                'location' => 'header',
                'description' => 'Test description',
            ]);
    }

    public function test_can_search_menus(): void
    {
        // Arrange
        $searchableMenu = Menu::factory()->create(['name' => 'Searchable Menu']);
        $otherMenu = Menu::factory()->create(['name' => 'Other Menu']);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\MenuResource\Pages\ListMenus::class)
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$searchableMenu])
            ->assertCanNotSeeTableRecords([$otherMenu]);
    }
}
