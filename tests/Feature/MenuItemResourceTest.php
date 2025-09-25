<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_menu_items(): void
    {
        $menu = Menu::factory()->create(['name' => 'Main Menu']);
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Home',
            'url' => '/',
            'is_visible' => true,
        ]);

        $this
            ->get('/admin/menu-items')
            ->assertOk()
            ->assertSee('Main Menu')
            ->assertSee('Home')
            ->assertSee('/');
    }

    public function test_can_create_menu_item(): void
    {
        $menu = Menu::factory()->create();

        $this
            ->get('/admin/menu-items/create')
            ->assertOk();

        $this->post('/admin/menu-items', [
            'menu_id' => $menu->id,
            'label' => 'About Us',
            'url' => '/about',
            'route_name' => 'about',
            'icon' => 'heroicon-o-information-circle',
            'sort_order' => 1,
            'is_visible' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'label' => 'About Us',
            'url' => '/about',
            'route_name' => 'about',
            'icon' => 'heroicon-o-information-circle',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }

    public function test_can_view_menu_item(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Contact',
        ]);

        $this
            ->get("/admin/menu-items/{$menuItem->id}")
            ->assertOk()
            ->assertSee('Contact');
    }

    public function test_can_edit_menu_item(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Services',
        ]);

        $this
            ->get("/admin/menu-items/{$menuItem->id}/edit")
            ->assertOk();

        $this->put("/admin/menu-items/{$menuItem->id}", [
            'menu_id' => $menu->id,
            'label' => 'Our Services',
            'url' => '/services',
            'route_name' => 'services',
            'icon' => 'heroicon-o-briefcase',
            'sort_order' => 2,
            'is_visible' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'label' => 'Our Services',
            'url' => '/services',
            'route_name' => 'services',
            'icon' => 'heroicon-o-briefcase',
            'sort_order' => 2,
            'is_visible' => false,
        ]);
    }

    public function test_can_delete_menu_item(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
        ]);

        $this
            ->delete("/admin/menu-items/{$menuItem->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('menu_items', [
            'id' => $menuItem->id,
        ]);
    }

    public function test_can_filter_menu_items_by_menu(): void
    {
        $menu1 = Menu::factory()->create(['name' => 'Main Menu']);
        $menu2 = Menu::factory()->create(['name' => 'Footer Menu']);

        MenuItem::factory()->create([
            'menu_id' => $menu1->id,
            'label' => 'Home',
        ]);

        MenuItem::factory()->create([
            'menu_id' => $menu2->id,
            'label' => 'Privacy Policy',
        ]);

        $this
            ->get('/admin/menu-items?menu_id='.$menu1->id)
            ->assertOk()
            ->assertSee('Home')
            ->assertDontSee('Privacy Policy');
    }

    public function test_can_filter_menu_items_by_parent(): void
    {
        $menu = Menu::factory()->create();
        $parentItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Products',
        ]);

        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem->id,
            'label' => 'Electronics',
        ]);

        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => null,
            'label' => 'About',
        ]);

        $this
            ->get('/admin/menu-items?parent_id='.$parentItem->id)
            ->assertOk()
            ->assertSee('Electronics')
            ->assertDontSee('About');
    }

    public function test_can_filter_menu_items_by_visibility(): void
    {
        $menu = Menu::factory()->create();

        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Visible Item',
            'is_visible' => true,
        ]);

        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Hidden Item',
            'is_visible' => false,
        ]);

        $this
            ->get('/admin/menu-items?is_visible=1')
            ->assertOk()
            ->assertSee('Visible Item')
            ->assertDontSee('Hidden Item');
    }

    public function test_menu_item_hierarchical_relationships(): void
    {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Products',
        ]);

        $child1 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'label' => 'Electronics',
        ]);

        $child2 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'label' => 'Clothing',
        ]);

        // Test parent relationship
        $this->assertEquals($parent->id, $child1->parent->id);
        $this->assertEquals($parent->id, $child2->parent->id);

        // Test children relationship
        $children = $parent->children;
        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($child1));
        $this->assertTrue($children->contains($child2));
    }

    public function test_menu_item_route_params_casting(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'route_params' => ['id' => 1, 'slug' => 'test'],
        ]);

        $this->assertIsArray($menuItem->route_params);
        $this->assertEquals(['id' => 1, 'slug' => 'test'], $menuItem->route_params);
    }

    public function test_menu_item_sort_order_casting(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'sort_order' => 5,
        ]);

        $this->assertIsInt($menuItem->sort_order);
        $this->assertEquals(5, $menuItem->sort_order);
    }

    public function test_menu_item_visibility_casting(): void
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'is_visible' => true,
        ]);

        $this->assertIsBool($menuItem->is_visible);
        $this->assertTrue($menuItem->is_visible);
    }

    public function test_can_create_nested_menu_structure(): void
    {
        $menu = Menu::factory()->create();

        // Create parent item
        $parent = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Products',
            'sort_order' => 1,
        ]);

        // Create child items
        $child1 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'label' => 'Electronics',
            'sort_order' => 1,
        ]);

        $child2 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'label' => 'Clothing',
            'sort_order' => 2,
        ]);

        // Create grandchild
        $grandchild = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $child1->id,
            'label' => 'Smartphones',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $parent->id,
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $child1->id,
            'parent_id' => $parent->id,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $grandchild->id,
            'parent_id' => $child1->id,
        ]);
    }

    public function test_menu_item_validation_rules(): void
    {
        $menu = Menu::factory()->create();

        // Test required fields
        $this
            ->post('/admin/menu-items', [])
            ->assertSessionHasErrors(['menu_id', 'label']);

        // Test valid data
        $this->post('/admin/menu-items', [
            'menu_id' => $menu->id,
            'label' => 'Valid Item',
        ])->assertRedirect();
    }

    public function test_menu_item_url_validation(): void
    {
        $menu = Menu::factory()->create();

        // Test invalid URL
        $this->post('/admin/menu-items', [
            'menu_id' => $menu->id,
            'label' => 'Invalid URL',
            'url' => 'not-a-valid-url',
        ])->assertSessionHasErrors(['url']);

        // Test valid URL
        $this->post('/admin/menu-items', [
            'menu_id' => $menu->id,
            'label' => 'Valid URL',
            'url' => 'https://example.com',
        ])->assertRedirect();
    }
}
