<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\MenuItem;

use function Pest\Laravel\get;

it('renders header menu items', function (): void {
    $menu = Menu::create(['key' => 'main_header', 'name' => 'Main Header', 'is_active' => true]);
    MenuItem::create(['menu_id' => $menu->id, 'label' => 'Test Link', 'url' => '/test', 'sort_order' => 1]);

    $response = get('/lt');
    $response->assertOk();
    $response->assertSee('Test Link', false);
});
