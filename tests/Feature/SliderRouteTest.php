<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SliderRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_sliders_index_route_exists(): void
    {
        $this->get('/admin/sliders')
            ->assertStatus(200);
    }

    public function test_sliders_create_route_exists(): void
    {
        $this->get('/admin/sliders/create')
            ->assertStatus(200);
    }

    public function test_sliders_route_names_are_correct(): void
    {
        $this->assertTrue(route('filament.admin.resources.sliders.index') !== null);
        $this->assertTrue(route('filament.admin.resources.sliders.create') !== null);
    }
}
