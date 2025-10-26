<?php

declare(strict_types=1);

namespace Tests\Admin\Pages;

use App\Filament\Pages\SearchExplorer;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SearchExplorerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_explorer_page_fetches_results(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $brand = Brand::factory()->create(['name' => 'Explorer Brand', 'is_enabled' => true]);

        Product::factory()->create([
            'name'         => 'Explorer Product',
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(SearchExplorer::class)
            ->set('query', 'Explorer Product')
            ->call('performSearch')
            ->assertSet('results.0.title', 'Explorer Product')
            ->assertSet('meta.query', 'Explorer Product');
    }
}
