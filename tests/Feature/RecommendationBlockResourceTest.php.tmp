<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecommendationBlockResource\Pages\CreateRecommendationBlock;
use App\Filament\Resources\RecommendationBlockResource\Pages\EditRecommendationBlock;
use App\Filament\Resources\RecommendationBlockResource\Pages\ListRecommendationBlocks;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class RecommendationBlockResourceTest extends TestCase
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

    public function test_can_list_recommendation_blocks(): void
    {
        RecommendationBlock::factory()->count(3)->create();

        Livewire::test(ListRecommendationBlocks::class)
            ->assertCanSeeTableRecords(RecommendationBlock::all());
    }

    public function test_can_create_recommendation_block(): void
    {
        $newData = RecommendationBlock::factory()->make();

        Livewire::test(CreateRecommendationBlock::class)
            ->fillForm([
                'name' => $newData->name,
                'title' => $newData->title,
                'description' => $newData->description,
                'type' => 'featured',
                'position' => 'top',
                'is_active' => true,
                'max_products' => 10,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_blocks', [
            'name' => $newData->name,
            'title' => $newData->title,
        ]);
    }

    public function test_can_edit_recommendation_block(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create();

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_blocks', [
            'id' => $recommendationBlock->id,
            'name' => 'Updated Name',
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_recommendation_block(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create();

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('recommendation_blocks', [
            'id' => $recommendationBlock->id,
        ]);
    }

    public function test_can_filter_recommendation_blocks_by_type(): void
    {
        RecommendationBlock::factory()->create(['type' => 'featured']);
        RecommendationBlock::factory()->create(['type' => 'related']);

        Livewire::test(ListRecommendationBlocks::class)
            ->filterTable('type', 'featured')
            ->assertCanSeeTableRecords(RecommendationBlock::where('type', 'featured')->get())
            ->assertCanNotSeeTableRecords(RecommendationBlock::where('type', 'related')->get());
    }

    public function test_can_filter_recommendation_blocks_by_position(): void
    {
        RecommendationBlock::factory()->create(['position' => 'top']);
        RecommendationBlock::factory()->create(['position' => 'bottom']);

        Livewire::test(ListRecommendationBlocks::class)
            ->filterTable('position', 'top')
            ->assertCanSeeTableRecords(RecommendationBlock::where('position', 'top')->get())
            ->assertCanNotSeeTableRecords(RecommendationBlock::where('position', 'bottom')->get());
    }

    public function test_can_filter_recommendation_blocks_by_active_status(): void
    {
        RecommendationBlock::factory()->create(['is_active' => true]);
        RecommendationBlock::factory()->create(['is_active' => false]);

        Livewire::test(ListRecommendationBlocks::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(RecommendationBlock::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(RecommendationBlock::where('is_active', false)->get());
    }

    public function test_can_search_recommendation_blocks_by_name(): void
    {
        RecommendationBlock::factory()->create(['name' => 'Featured Products']);
        RecommendationBlock::factory()->create(['name' => 'Related Items']);

        Livewire::test(ListRecommendationBlocks::class)
            ->searchTable('Featured')
            ->assertCanSeeTableRecords(RecommendationBlock::where('name', 'like', '%Featured%')->get())
            ->assertCanNotSeeTableRecords(RecommendationBlock::where('name', 'like', '%Related%')->get());
    }

    public function test_can_bulk_delete_recommendation_blocks(): void
    {
        $recommendationBlocks = RecommendationBlock::factory()->count(3)->create();

        Livewire::test(ListRecommendationBlocks::class)
            ->callTableBulkAction('delete', $recommendationBlocks)
            ->assertHasNoTableBulkActionErrors();

        foreach ($recommendationBlocks as $block) {
            $this->assertDatabaseMissing('recommendation_blocks', [
                'id' => $block->id,
            ]);
        }
    }

    public function test_can_toggle_recommendation_block_active_status(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create(['is_active' => false]);

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->callAction('toggle_active')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_blocks', [
            'id' => $recommendationBlock->id,
            'is_active' => true,
        ]);
    }

    public function test_can_set_recommendation_block_as_default(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create(['is_default' => false]);

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->callAction('set_default')
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('recommendation_blocks', [
            'id' => $recommendationBlock->id,
            'is_default' => true,
        ]);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::test(CreateRecommendationBlock::class)
            ->fillForm([
                'name' => '',
                'title' => 'Test Title',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_requires_title(): void
    {
        Livewire::test(CreateRecommendationBlock::class)
            ->fillForm([
                'name' => 'test-block',
                'title' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    }

    public function test_validation_requires_unique_name(): void
    {
        RecommendationBlock::factory()->create(['name' => 'existing-block']);

        Livewire::test(CreateRecommendationBlock::class)
            ->fillForm([
                'name' => 'existing-block',
                'title' => 'Test Title',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_can_see_products_relationship(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create();
        $products = Product::factory()->count(3)->create();
        $recommendationBlock->products()->attach($products);

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->assertFormSet([
                'product_ids' => $products->pluck('id')->toArray(),
            ]);
    }

    public function test_can_attach_products_to_recommendation_block(): void
    {
        $recommendationBlock = RecommendationBlock::factory()->create();
        $products = Product::factory()->count(2)->create();

        Livewire::test(EditRecommendationBlock::class, [
            'record' => $recommendationBlock->getRouteKey(),
        ])
            ->fillForm([
                'product_ids' => $products->pluck('id')->toArray(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('recommendation_block_products', [
            'recommendation_block_id' => $recommendationBlock->id,
            'product_id' => $products->first()->id,
        ]);
    }
}
