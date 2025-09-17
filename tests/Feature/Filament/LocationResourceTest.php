<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_list_page_renders(): void
    {
        Location::factory()->count(5)->create();
        
        $response = $this->get(route('filament.admin.resources.locations.index'));
        
        $response->assertOk();
    }

    public function test_location_create_page_renders(): void
    {
        $response = $this->get(route('filament.admin.resources.locations.create'));
        
        $response->assertOk();
    }
}