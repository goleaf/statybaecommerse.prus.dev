<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationConfigResource\Pages\CreateRecommendationConfig;
use App\Filament\Resources\RecommendationConfigResource\Pages\EditRecommendationConfig;
use App\Filament\Resources\RecommendationConfigResource\Pages\ListRecommendationConfigs;
use App\Filament\Resources\RecommendationConfigResource\Pages\ViewRecommendationConfig;
use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecommendationConfigResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_recommendation_configs(): void
    {
        RecommendationConfig::factory()->count(3)->create();

        Livewire::test(ListRecommendationConfigs::class)
            ->assertCanSeeTableRecords(RecommendationConfig::all());
    }

    public function test_can_create_recommendation_config(): void
    {
        $newData = RecommendationConfig::factory()->make();

        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => $newData->name,
                'type' => 'collaborative',
                'description' => $newData->description,
                'min_score' => 0.1,
                'max_results' => 10,
                'decay_factor' => 0.9,
                'priority' => 0,
                'is_active' => true,
                'is_default' => false,
                'cache_ttl' => 60,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_configs', [
            'name' => $newData->name,
            'type' => 'collaborative',
        ]);
    }

    public function test_can_edit_recommendation_config(): void
    {
        $config = RecommendationConfig::factory()->create();

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Config',
                'min_score' => 0.2,
                'max_results' => 20,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_configs', [
            'id' => $config->id,
            'name' => 'Updated Config',
            'min_score' => 0.2,
            'max_results' => 20,
        ]);
    }

    public function test_can_delete_recommendation_config(): void
    {
        $config = RecommendationConfig::factory()->create();

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('recommendation_configs', [
            'id' => $config->id,
        ]);
    }

    public function test_can_view_recommendation_config(): void
    {
        $config = RecommendationConfig::factory()->create();

        Livewire::test(ViewRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$config]);
    }

    public function test_can_filter_recommendation_configs_by_type(): void
    {
        RecommendationConfig::factory()->create(['type' => 'collaborative']);
        RecommendationConfig::factory()->create(['type' => 'content_based']);

        Livewire::test(ListRecommendationConfigs::class)
            ->filterTable('type', 'collaborative')
            ->assertCanSeeTableRecords(RecommendationConfig::where('type', 'collaborative')->get())
            ->assertCanNotSeeTableRecords(RecommendationConfig::where('type', 'content_based')->get());
    }

    public function test_can_filter_recommendation_configs_by_active_status(): void
    {
        RecommendationConfig::factory()->create(['is_active' => true]);
        RecommendationConfig::factory()->create(['is_active' => false]);

        Livewire::test(ListRecommendationConfigs::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(RecommendationConfig::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfig::where('is_active', false)->get());
    }

    public function test_can_filter_recommendation_configs_by_default_status(): void
    {
        RecommendationConfig::factory()->create(['is_default' => true]);
        RecommendationConfig::factory()->create(['is_default' => false]);

        Livewire::test(ListRecommendationConfigs::class)
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords(RecommendationConfig::where('is_default', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfig::where('is_default', false)->get());
    }

    public function test_can_search_recommendation_configs_by_name(): void
    {
        RecommendationConfig::factory()->create(['name' => 'Collaborative Filtering']);
        RecommendationConfig::factory()->create(['name' => 'Content Based']);

        Livewire::test(ListRecommendationConfigs::class)
            ->searchTable('Collaborative')
            ->assertCanSeeTableRecords(RecommendationConfig::where('name', 'like', '%Collaborative%')->get())
            ->assertCanNotSeeTableRecords(RecommendationConfig::where('name', 'like', '%Content%')->get());
    }

    public function test_can_bulk_delete_recommendation_configs(): void
    {
        $configs = RecommendationConfig::factory()->count(3)->create();

        Livewire::test(ListRecommendationConfigs::class)
            ->callTableBulkAction('delete', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseMissing('recommendation_configs', [
                'id' => $config->id,
            ]);
        }
    }

    public function test_can_bulk_activate_recommendation_configs(): void
    {
        $configs = RecommendationConfig::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListRecommendationConfigs::class)
            ->callTableBulkAction('activate', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseHas('recommendation_configs', [
                'id' => $config->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_recommendation_configs(): void
    {
        $configs = RecommendationConfig::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(ListRecommendationConfigs::class)
            ->callTableBulkAction('deactivate', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseHas('recommendation_configs', [
                'id' => $config->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_toggle_recommendation_config_active_status(): void
    {
        $config = RecommendationConfig::factory()->create(['is_active' => false]);

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('toggle_active')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_configs', [
            'id' => $config->id,
            'is_active' => true,
        ]);
    }

    public function test_can_set_recommendation_config_as_default(): void
    {
        $config = RecommendationConfig::factory()->create(['is_default' => false]);

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('set_default')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_configs', [
            'id' => $config->id,
            'is_default' => true,
        ]);
    }

    public function test_setting_default_removes_other_defaults(): void
    {
        $existingDefault = RecommendationConfig::factory()->create(['is_default' => true]);
        $newDefault = RecommendationConfig::factory()->create(['is_default' => false]);

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $newDefault->getRouteKey(),
        ])
            ->callAction('set_default')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_configs', [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('recommendation_configs', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => '',
                'type' => 'collaborative',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_requires_type(): void
    {
        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => 'Test Config',
                'type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['type' => 'required']);
    }

    public function test_validation_requires_unique_name(): void
    {
        RecommendationConfig::factory()->create(['name' => 'existing-config']);

        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => 'existing-config',
                'type' => 'collaborative',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_validation_min_score_must_be_numeric(): void
    {
        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => 'Test Config',
                'type' => 'collaborative',
                'min_score' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['min_score' => 'numeric']);
    }

    public function test_validation_max_results_must_be_numeric(): void
    {
        Livewire::test(CreateRecommendationConfig::class)
            ->fillForm([
                'name' => 'Test Config',
                'type' => 'collaborative',
                'max_results' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['max_results' => 'numeric']);
    }

    public function test_can_see_products_relationship(): void
    {
        $config = RecommendationConfig::factory()->create();
        $products = Product::factory()->count(3)->create();
        $config->products()->attach($products);

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->assertFormSet([
                'products' => $products->pluck('id')->toArray(),
            ]);
    }

    public function test_can_attach_products_to_recommendation_config(): void
    {
        $config = RecommendationConfig::factory()->create();
        $products = Product::factory()->count(2)->create();

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'products' => $products->pluck('id')->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_products', [
            'recommendation_config_id' => $config->id,
            'product_id' => $products->first()->id,
        ]);
    }

    public function test_can_see_categories_relationship(): void
    {
        $config = RecommendationConfig::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $config->categories()->attach($categories);

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->assertFormSet([
                'categories' => $categories->pluck('id')->toArray(),
            ]);
    }

    public function test_can_attach_categories_to_recommendation_config(): void
    {
        $config = RecommendationConfig::factory()->create();
        $categories = Category::factory()->count(2)->create();

        Livewire::test(EditRecommendationConfig::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'categories' => $categories->pluck('id')->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_categories', [
            'recommendation_config_id' => $config->id,
            'category_id' => $categories->first()->id,
        ]);
    }

    public function test_can_see_analytics_relationship(): void
    {
        $config = RecommendationConfig::factory()->create();

        // This would test the analytics relationship if it exists
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $config->analytics());
    }

    public function test_can_scope_active_configs(): void
    {
        RecommendationConfig::factory()->create(['is_active' => true]);
        RecommendationConfig::factory()->create(['is_active' => false]);

        $activeConfigs = RecommendationConfig::active()->get();

        $this->assertCount(1, $activeConfigs);
        $this->assertTrue($activeConfigs->first()->is_active);
    }

    public function test_can_scope_by_type(): void
    {
        RecommendationConfig::factory()->create(['type' => 'collaborative']);
        RecommendationConfig::factory()->create(['type' => 'content_based']);

        $collaborativeConfigs = RecommendationConfig::byType('collaborative')->get();

        $this->assertCount(1, $collaborativeConfigs);
        $this->assertEquals('collaborative', $collaborativeConfigs->first()->type);
    }
}
