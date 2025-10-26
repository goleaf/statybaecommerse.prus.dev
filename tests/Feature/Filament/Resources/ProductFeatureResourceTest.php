<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductFeatureResource\Pages\CreateProductFeature;
use App\Filament\Resources\ProductFeatureResource\Pages\EditProductFeature;
use App\Filament\Resources\ProductFeatureResource\Pages\ListProductFeatures;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductFeatureResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->product = Product::factory()->create([
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_product_features(): void
    {
        $feature = ProductFeature::factory()->create([
            'product_id'    => $this->product->id,
            'feature_type'  => 'benefit',
            'feature_key'   => 'battery',
            'feature_value' => 10.5,
        ]);

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->assertSee('battery')
            ->searchTable('battery')
            ->assertSee('battery');
    }

    public function test_can_create_product_feature(): void
    {
        Livewire::test(CreateProductFeature::class)
            ->fillForm([
                'product_id'    => $this->product->id,
                'feature_type'  => 'specification',
                'feature_key'   => 'weight',
                'feature_value' => 1.2,
                'weight'        => 5,
                'is_active'     => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'product_id'    => $this->product->id,
            'feature_type'  => 'specification',
            'feature_key'   => 'weight',
            'feature_value' => 1.2,
            'weight'        => 5,
            'is_active'     => true,
        ]);
    }

    public function test_can_edit_product_feature(): void
    {
        $feature = ProductFeature::factory()->create([
            'product_id'    => $this->product->id,
            'feature_type'  => 'specification',
            'feature_key'   => 'color',
            'feature_value' => 1.0,
            'weight'        => 1,
        ]);

        Livewire::test(EditProductFeature::class, ['record' => $feature->getRouteKey()])
            ->fillForm([
                'feature_value' => 2.5,
                'weight'        => 10,
                'is_active'     => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'id'            => $feature->id,
            'feature_value' => 2.5,
            'weight'        => 10,
            'is_active'     => false,
        ]);
    }

    public function test_can_delete_product_feature(): void
    {
        $features = collect([
            ProductFeature::factory()->create([
                'product_id'    => $this->product->id,
                'feature_value' => 1.0,
            ]),
            ProductFeature::factory()->create([
                'product_id'    => $this->product->id,
                'feature_value' => 2.0,
            ]),
        ]);

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $features->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($features as $feature) {
            $this->assertDatabaseMissing('product_features', ['id' => $feature->id]);
        }
    }

    public function test_can_filter_features_by_type_and_product(): void
    {
        $matching = ProductFeature::factory()->create([
            'product_id'    => $this->product->id,
            'feature_type'  => 'performance',
            'feature_value' => 5.0,
        ]);

        $otherFeature = ProductFeature::factory()->create([
            'feature_type'  => 'benefit',
            'feature_value' => 3.0,
        ]);

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->filterTable('feature_type', 'performance')
            ->filterTable('product_id', $this->product->id)
            ->assertSee((string) $matching->feature_value)
            ->assertDontSee((string) $otherFeature->feature_value);
    }

    public function test_can_bulk_delete_product_features(): void
    {
        $features = ProductFeature::factory()->count(3)->create([
            'product_id'    => $this->product->id,
            'feature_value' => 1.0,
        ]);

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $features->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($features as $feature) {
            $this->assertDatabaseMissing('product_features', ['id' => $feature->id]);
        }
    }

    public function test_can_filter_features_by_active_state(): void
    {
        $activeFeature = ProductFeature::factory()->create([
            'product_id'   => $this->product->id,
            'feature_type' => 'performance',
            'is_active'    => true,
        ]);

        $inactiveFeature = ProductFeature::factory()->create([
            'product_id'   => $this->product->id,
            'feature_type' => 'benefit',
            'is_active'    => false,
        ]);

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->filterTable('is_active', true)
            ->assertSee('Performance')
            ->assertDontSee('Benefit');

        Livewire::test(ListProductFeatures::class)
            ->call('loadTable')
            ->filterTable('is_active', false)
            ->assertSee('Benefit')
            ->assertDontSee('Performance');
    }
}
