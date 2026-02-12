<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Category;

use App\Livewire\Pages\Category\Index;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSet('isIndex', true);
    }

    public function test_localized_categories_route_renders_when_visible_categories_exist(): void
    {
        Category::factory()->create([
            'is_visible' => true,
        ]);

        $response = $this->get('/lt/categories');

        $response->assertSuccessful();
    }
}
