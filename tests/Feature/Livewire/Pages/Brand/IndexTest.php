<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Brand;

use App\Livewire\Pages\Brand\Index;
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
}
