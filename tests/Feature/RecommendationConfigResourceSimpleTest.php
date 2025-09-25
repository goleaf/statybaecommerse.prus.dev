<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\CreateRecommendationConfigSimple;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\EditRecommendationConfigSimple;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigsSimple;
use App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ViewRecommendationConfigSimple;
use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationConfigSimple;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RecommendationConfigResourceSimpleTest extends TestCase
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

    public function test_can_list_recommendation_configs_simple(): void
    {
        RecommendationConfigSimple::factory()->count(3)->create();

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->assertCanSeeTableRecords(RecommendationConfigSimple::all());
    }

    public function test_can_create_recommendation_config_simple(): void
    {
        $newData = RecommendationConfigSimple::factory()->make();

        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => $newData->name,
                'code' => 'test-config',
                'description' => $newData->description,
                'algorithm_type' => 'collaborative',
                'min_score' => 0.1,
                'max_results' => 10,
                'decay_factor' => 0.9,
                'exclude_out_of_stock' => true,
                'exclude_inactive' => true,
                'price_weight' => 0.2,
                'rating_weight' => 0.3,
                'popularity_weight' => 0.2,
                'recency_weight' => 0.1,
                'category_weight' => 0.2,
                'custom_weight' => 0.0,
                'cache_duration' => 60,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 0,
                'notes' => 'Test notes',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_simples', [
            'name' => $newData->name,
            'code' => 'test-config',
            'algorithm_type' => 'collaborative',
        ]);
    }

    public function test_can_edit_recommendation_config_simple(): void
    {
        $config = RecommendationConfigSimple::factory()->create();

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Config',
                'min_score' => 0.2,
                'max_results' => 20,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_simples', [
            'id' => $config->id,
            'name' => 'Updated Config',
            'min_score' => 0.2,
            'max_results' => 20,
        ]);
    }

    public function test_can_delete_recommendation_config_simple(): void
    {
        $config = RecommendationConfigSimple::factory()->create();

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('recommendation_config_simples', [
            'id' => $config->id,
        ]);
    }

    public function test_can_view_recommendation_config_simple(): void
    {
        $config = RecommendationConfigSimple::factory()->create();

        Livewire::test(ViewRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$config])
            ->assertSee($config->name)
            ->assertSee($config->code);
    }

    public function test_can_filter_recommendation_configs_simple_by_algorithm_type(): void
    {
        RecommendationConfigSimple::factory()->create(['algorithm_type' => 'collaborative']);
        RecommendationConfigSimple::factory()->create(['algorithm_type' => 'content_based']);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->filterTable('algorithm_type', 'collaborative')
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('algorithm_type', 'collaborative')->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('algorithm_type', 'content_based')->get());
    }

    public function test_can_filter_recommendation_configs_simple_by_active_status(): void
    {
        RecommendationConfigSimple::factory()->create(['is_active' => true]);
        RecommendationConfigSimple::factory()->create(['is_active' => false]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('is_active', false)->get());
    }

    public function test_can_filter_recommendation_configs_simple_by_default_status(): void
    {
        RecommendationConfigSimple::factory()->create(['is_default' => true]);
        RecommendationConfigSimple::factory()->create(['is_default' => false]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('is_default', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('is_default', false)->get());
    }

    public function test_can_filter_recommendation_configs_simple_by_exclude_out_of_stock(): void
    {
        RecommendationConfigSimple::factory()->create(['exclude_out_of_stock' => true]);
        RecommendationConfigSimple::factory()->create(['exclude_out_of_stock' => false]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->filterTable('exclude_out_of_stock', true)
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('exclude_out_of_stock', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('exclude_out_of_stock', false)->get());
    }

    public function test_can_filter_recommendation_configs_simple_by_exclude_inactive(): void
    {
        RecommendationConfigSimple::factory()->create(['exclude_inactive' => true]);
        RecommendationConfigSimple::factory()->create(['exclude_inactive' => false]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->filterTable('exclude_inactive', true)
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('exclude_inactive', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('exclude_inactive', false)->get());
    }

    public function test_can_search_recommendation_configs_simple_by_name(): void
    {
        RecommendationConfigSimple::factory()->create(['name' => 'Collaborative Filtering']);
        RecommendationConfigSimple::factory()->create(['name' => 'Content Based']);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->searchTable('Collaborative')
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('name', 'like', '%Collaborative%')->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('name', 'like', '%Content%')->get());
    }

    public function test_can_search_recommendation_configs_simple_by_code(): void
    {
        RecommendationConfigSimple::factory()->create(['code' => 'collab-filter']);
        RecommendationConfigSimple::factory()->create(['code' => 'content-based']);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->searchTable('collab')
            ->assertCanSeeTableRecords(RecommendationConfigSimple::where('code', 'like', '%collab%')->get())
            ->assertCanNotSeeTableRecords(RecommendationConfigSimple::where('code', 'like', '%content%')->get());
    }

    public function test_can_bulk_delete_recommendation_configs_simple(): void
    {
        $configs = RecommendationConfigSimple::factory()->count(3)->create();

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->callTableBulkAction('delete', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseMissing('recommendation_config_simples', [
                'id' => $config->id,
            ]);
        }
    }

    public function test_can_bulk_activate_recommendation_configs_simple(): void
    {
        $configs = RecommendationConfigSimple::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->callTableBulkAction('activate', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseHas('recommendation_config_simples', [
                'id' => $config->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_recommendation_configs_simple(): void
    {
        $configs = RecommendationConfigSimple::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(ListRecommendationConfigsSimple::class)
            ->callTableBulkAction('deactivate', $configs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($configs as $config) {
            $this->assertDatabaseHas('recommendation_config_simples', [
                'id' => $config->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_toggle_recommendation_config_simple_active_status(): void
    {
        $config = RecommendationConfigSimple::factory()->create(['is_active' => false]);

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('toggle_active')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_config_simples', [
            'id' => $config->id,
            'is_active' => true,
        ]);
    }

    public function test_can_set_recommendation_config_simple_as_default(): void
    {
        $config = RecommendationConfigSimple::factory()->create(['is_default' => false]);

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->callAction('set_default')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_config_simples', [
            'id' => $config->id,
            'is_default' => true,
        ]);
    }

    public function test_setting_default_removes_other_defaults(): void
    {
        $existingDefault = RecommendationConfigSimple::factory()->create(['is_default' => true]);
        $newDefault = RecommendationConfigSimple::factory()->create(['is_default' => false]);

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $newDefault->getRouteKey(),
        ])
            ->callAction('set_default')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_config_simples', [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('recommendation_config_simples', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => '',
                'code' => 'test-config',
                'algorithm_type' => 'collaborative',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_requires_code(): void
    {
        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => 'Test Config',
                'code' => '',
                'algorithm_type' => 'collaborative',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'required']);
    }

    public function test_validation_requires_unique_code(): void
    {
        RecommendationConfigSimple::factory()->create(['code' => 'existing-code']);

        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => 'Test Config',
                'code' => 'existing-code',
                'algorithm_type' => 'collaborative',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_validation_requires_algorithm_type(): void
    {
        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => 'Test Config',
                'code' => 'test-config',
                'algorithm_type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['algorithm_type' => 'required']);
    }

    public function test_validation_min_score_must_be_numeric(): void
    {
        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => 'Test Config',
                'code' => 'test-config',
                'algorithm_type' => 'collaborative',
                'min_score' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['min_score' => 'numeric']);
    }

    public function test_validation_max_results_must_be_numeric(): void
    {
        Livewire::test(CreateRecommendationConfigSimple::class)
            ->fillForm([
                'name' => 'Test Config',
                'code' => 'test-config',
                'algorithm_type' => 'collaborative',
                'max_results' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['max_results' => 'numeric']);
    }

    public function test_can_see_products_relationship(): void
    {
        $config = RecommendationConfigSimple::factory()->create();
        $products = Product::factory()->count(3)->create();
        $config->products()->attach($products);

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])->tap(function ($livewire) use ($products): void {
            $state = $livewire->form->getState()['products'] ?? [];
            $this->assertEqualsCanonicalizing(
                $products->pluck('id')->map(fn ($id) => (string) $id)->all(),
                array_map(fn ($value) => (string) $value, $state)
            );
        });
    }

    public function test_can_attach_products_to_recommendation_config_simple(): void
    {
        $config = RecommendationConfigSimple::factory()->create();
        $products = Product::factory()->count(2)->create();

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'products' => $products->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_simple_products', [
            'recommendation_config_simple_id' => $config->id,
            'product_id' => $products->first()->id,
        ]);
    }

    public function test_can_see_categories_relationship(): void
    {
        $config = RecommendationConfigSimple::factory()->create();
        $categories = Category::factory()->count(3)->create();
        $config->categories()->attach($categories);

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->assertFormSet([
                'categories' => $categories->pluck('id')->toArray(),
            ]);
    }

    public function test_can_attach_categories_to_recommendation_config_simple(): void
    {
        $config = RecommendationConfigSimple::factory()->create();
        $categories = Category::factory()->count(2)->create();

        Livewire::test(EditRecommendationConfigSimple::class, [
            'record' => $config->getRouteKey(),
        ])
            ->fillForm([
                'categories' => $categories->pluck('id')->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_config_simple_categories', [
            'recommendation_config_simple_id' => $config->id,
            'category_id' => $categories->first()->id,
        ]);
    }

    public function test_can_see_analytics_relationship(): void
    {
        $config = RecommendationConfigSimple::factory()->create();

        // This would test the analytics relationship if it exists
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $config->analytics());
    }

    public function test_can_scope_active_configs(): void
    {
        RecommendationConfigSimple::factory()->create(['is_active' => true]);
        RecommendationConfigSimple::factory()->create(['is_active' => false]);

        $activeConfigs = RecommendationConfigSimple::active()->get();

        $this->assertCount(1, $activeConfigs);
        $this->assertTrue($activeConfigs->first()->is_active);
    }

    public function test_can_scope_default_configs(): void
    {
        RecommendationConfigSimple::factory()->create(['is_default' => true]);
        RecommendationConfigSimple::factory()->create(['is_default' => false]);

        $defaultConfigs = RecommendationConfigSimple::default()->get();

        $this->assertCount(1, $defaultConfigs);
        $this->assertTrue($defaultConfigs->first()->is_default);
    }

    public function test_can_scope_by_algorithm_type(): void
    {
        RecommendationConfigSimple::factory()->create(['algorithm_type' => 'collaborative']);
        RecommendationConfigSimple::factory()->create(['algorithm_type' => 'content_based']);

        $collaborativeConfigs = RecommendationConfigSimple::byAlgorithmType('collaborative')->get();

        $this->assertCount(1, $collaborativeConfigs);
        $this->assertEquals('collaborative', $collaborativeConfigs->first()->algorithm_type);
    }

    public function test_can_scope_ordered(): void
    {
        RecommendationConfigSimple::factory()->create(['name' => 'Z Config', 'sort_order' => 2]);
        RecommendationConfigSimple::factory()->create(['name' => 'A Config', 'sort_order' => 1]);

        $orderedConfigs = RecommendationConfigSimple::ordered()->get();

        $this->assertEquals('A Config', $orderedConfigs->first()->name);
        $this->assertEquals('Z Config', $orderedConfigs->last()->name);
    }
}
