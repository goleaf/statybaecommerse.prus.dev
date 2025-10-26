<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Channel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class ChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_cover_core_channel_columns(): void
    {
        $model = new Channel();

        $this->assertContains('name', $model->getFillable());
        $this->assertContains('code', $model->getFillable());
        $this->assertContains('sort_order', $model->getFillable());
    }

    public function test_casts_configuration_handles_toggles_and_metadata(): void
    {
        $casts = (new Channel())->getCasts();

        $this->assertSame('boolean', $casts['is_enabled'] ?? null);
        $this->assertSame('boolean', $casts['is_default'] ?? null);
        $this->assertSame('integer', $casts['sort_order'] ?? null);
        $this->assertSame('array', $casts['metadata'] ?? null);
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        $alpha = Channel::factory()->create(['name' => 'Alpha', 'sort_order' => 2]);
        $zulu = Channel::factory()->create(['name' => 'Zulu', 'sort_order' => 1]);

        $orderedNames = Channel::query()->orderedByName()->pluck('name');

        $this->assertInstanceOf(Collection::class, $orderedNames);
        $this->assertSame([$alpha->name, $zulu->name], $orderedNames->all());
    }

    public function test_products_relationship_uses_belongs_to_many(): void
    {
        $channel = Channel::factory()->create();
        $product = Product::factory()->create();

        $channel->products()->attach($product);

        $this->assertInstanceOf(BelongsToMany::class, $channel->products());
        $this->assertTrue($channel->products->first()->is($product));
    }
}
