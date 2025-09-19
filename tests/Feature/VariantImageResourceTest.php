<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\VariantImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class VariantImageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_render_variant_image_resource(): void
    {
        $this->get('/admin/variant-images')
            ->assertOk();
    }

    public function test_can_create_variant_image(): void
    {
        $variant = ProductVariant::factory()->create();
        
        $data = [
            'variant_id' => $variant->id,
            'image_path' => ['test-image.jpg'],
            'alt_text' => 'Test Image',
            'sort_order' => 1,
            'is_primary' => true,
        ];

        Livewire::test('App\Filament\Resources\VariantImageResource\Pages\CreateVariantImage')
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_images', [
            'variant_id' => $variant->id,
            'image_path' => 'test-image.jpg',
            'alt_text' => 'Test Image',
            'sort_order' => 1,
            'is_primary' => true,
        ]);
    }

    public function test_can_edit_variant_image(): void
    {
        $variantImage = VariantImage::factory()->create();
        
        $data = [
            'image_path' => ['updated-image.jpg'],
            'alt_text' => 'Updated Alt Text',
            'sort_order' => 2,
            'is_primary' => false,
        ];

        Livewire::test('App\Filament\Resources\VariantImageResource\Pages\EditVariantImage', [
            'record' => $variantImage->id,
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_images', [
            'id' => $variantImage->id,
            'image_path' => 'updated-image.jpg',
            'alt_text' => 'Updated Alt Text',
            'sort_order' => 2,
            'is_primary' => false,
        ]);
    }

    public function test_can_delete_variant_image(): void
    {
        $variantImage = VariantImage::factory()->create();

        Livewire::test('App\Filament\Resources\VariantImageResource\Pages\EditVariantImage', [
            'record' => $variantImage->id,
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('variant_images', [
            'id' => $variantImage->id,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        Livewire::test('App\Filament\Resources\VariantImageResource\Pages\CreateVariantImage')
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['variant_id', 'image_path']);
    }
}
