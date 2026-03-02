<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Brand;

use App\Livewire\Pages\Brand\Index;
use App\Models\Brand;
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
            ->assertSet('sortBy', 'name');
    }

    public function test_it_renders_when_active_filters_are_applied(): void
    {
        Livewire::test(Index::class)
            ->set('sortBy', 'featured')
            ->assertStatus(200);
    }

    public function test_localized_brands_route_renders_successfully(): void
    {
        $response = $this->get('/lt/brands');

        $response->assertSuccessful();
    }

    public function test_pagination_links_use_root_relative_locale_path(): void
    {
        Brand::factory()->count(13)->create([
            'is_enabled' => true,
        ]);

        $response = $this->get('/lt/brands');

        $response->assertSuccessful()
            ->assertSee('href="/lt/brands?page=2"', false)
            ->assertDontSee('href="lt/brands?page=2"', false);
    }
}
