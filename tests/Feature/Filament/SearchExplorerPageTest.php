<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\SearchExplorer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SearchExplorerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_perform_search_resets_state_for_blank_query(): void
    {
        // Authenticate so the Filament panel grants access to the page.
        $this->resolveAdminPanel();
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $component = Livewire::test(SearchExplorer::class)
            ->set('query', '   ')
            ->set('perPage', 999)
            ->call('performSearch');

        // The component should reset to the default empty state when no query is provided.
        $component->assertSet('results', [])
            ->assertSet('meta.total_results', 0)
            ->assertSet('perPage', 50) // clamped to SearchQueryData::MAX_PER_PAGE
            ->assertSet('buckets.product', 0);
    }

    public function test_perform_search_returns_database_results(): void
    {
        $this->resolveAdminPanel();
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        // Seed a visible product so the database-backed search has real data to surface.
        Product::factory()->create([
            'name'         => 'Laptop Pro',
            'slug'         => 'laptop-pro',
            'sku'          => 'LAPTOP-1',
            'price'        => 1499.00,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(SearchExplorer::class)
            ->set('query', 'laptop')
            ->set('perPage', 10)
            ->call('performSearch')
            ->assertSet('results.0.type', 'product')
            ->assertSet('meta.total_results', fn ($total) => $total >= 1)
            ->assertSet('buckets.product', fn ($count) => $count >= 1);
    }

    public function test_navigation_registration_requires_admin(): void
    {
        $this->assertFalse(SearchExplorer::shouldRegisterNavigation());

        $this->resolveAdminPanel();
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $this->assertTrue(SearchExplorer::shouldRegisterNavigation());
    }
}
