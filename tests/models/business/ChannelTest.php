<?php

declare(strict_types=1);

namespace Tests\Models\Business;

use App\Models\Channel;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_channel_with_expected_defaults(): void
    {
        // Act: create a fresh channel using the factory so we exercise realistic defaults.
        $channel = Channel::factory()->create();

        // Assert: the record persists and exposes the core defaults surfaced in the UI.
        $this->assertDatabaseHas('channels', ['id' => $channel->id]);
        $this->assertTrue($channel->is_enabled, 'Factory channels should be enabled so storefront tests succeed.');
        $this->assertSame('EUR', $channel->currency_code, 'Factory keeps EUR to align with seeding defaults.');
    }

    public function test_casts_and_fillable_configuration(): void
    {
        // Arrange: provide toggle and json data to ensure casts/assignment stay in sync with the schema.
        $channel = Channel::factory()->create([
            'is_enabled'        => true,
            'is_default'        => false,
            'is_active'         => true,
            'ssl_enabled'       => true,
            'analytics_enabled' => true,
            'metadata'          => ['theme' => 'dark'],
            'configuration'     => ['timezone' => 'Europe/Vilnius'],
        ]);

        // Assert: every expected attribute may be mass assigned.
        foreach (['name', 'slug', 'code', 'type', 'metadata', 'configuration'] as $attribute) {
            $this->assertContains($attribute, $channel->getFillable(), sprintf('Attribute %s should be fillable.', $attribute));
        }

        // Assert: boolean and array casts remain typed after persistence.
        $this->assertIsBool($channel->is_enabled);
        $this->assertIsBool($channel->is_default);
        $this->assertIsBool($channel->is_active);
        $this->assertIsBool($channel->ssl_enabled);
        $this->assertIsBool($channel->analytics_enabled);
        $this->assertIsArray($channel->metadata);
        $this->assertIsArray($channel->configuration);
    }

    public function test_relationships_expose_expected_types_and_data(): void
    {
        // Arrange: build a channel and related records to verify the relations and eager loading metadata.
        $channel = Channel::factory()->create();
        $order = Order::factory()->create(['channel_id' => $channel->getKey()]);
        $discount = Discount::factory()->create(['channel_id' => $channel->getKey()]);
        $product = Product::factory()->create();
        $channel->products()->attach($product->getKey());

        // Assert: relation accessors return the correct relation instances for IDE support.
        $this->assertInstanceOf(HasMany::class, $channel->orders());
        $this->assertInstanceOf(HasMany::class, $channel->discounts());
        $this->assertInstanceOf(BelongsToMany::class, $channel->products());

        // Assert: data retrieved from the relationships is complete and unfiltered by scopes.
        $this->assertTrue($channel->orders->contains($order));
        $this->assertTrue($channel->discounts->contains($discount));
        $this->assertTrue($channel->products->contains($product));
    }

    public function test_boolean_scopes_filter_records_correctly(): void
    {
        // Arrange: capture a mix of channel records to validate each scope independently.
        $enabled = Channel::factory()->create(['is_enabled' => true, 'is_default' => false, 'is_active' => true]);
        $default = Channel::factory()->create(['is_enabled' => true, 'is_default' => true, 'is_active' => true]);
        $inactive = Channel::factory()->create(['is_enabled' => false, 'is_default' => false, 'is_active' => false]);
        $api = Channel::factory()->create(['type' => 'api']);

        // Assert: Enabled scope only returns channels toggled on.
        $this->assertTrue(Channel::enabled()->get()->contains($enabled));
        $this->assertFalse(Channel::enabled()->get()->contains($inactive));

        // Assert: Default scope matches the default record and ignores others.
        $this->assertTrue(Channel::default()->get()->contains($default));
        $this->assertFalse(Channel::default()->get()->contains($enabled));

        // Assert: Active scope respects operational toggles.
        $this->assertTrue(Channel::active()->get()->contains($enabled));
        $this->assertFalse(Channel::active()->get()->contains($inactive));

        // Assert: Type scope narrows down to the requested delivery mechanism.
        $this->assertTrue(Channel::byType('api')->get()->contains($api));
        $this->assertFalse(Channel::byType('api')->get()->contains($enabled));
    }

    public function test_ordered_scope_prioritises_sort_order_then_name(): void
    {
        // Arrange: share a constant sort order to confirm the tie-breaker uses names.
        $alpha = Channel::factory()->create(['sort_order' => 5, 'name' => 'Alpha Channel']);
        $bravo = Channel::factory()->create(['sort_order' => 5, 'name' => 'Bravo Channel']);
        $first = Channel::factory()->create(['sort_order' => 1, 'name' => 'Zulu Channel']);

        // Act: execute the ordered scope.
        $ordered = Channel::ordered()->get();

        // Assert: lowest explicit sort order wins, followed by alphabetical tiebreakers.
        $this->assertSame($first->getKey(), $ordered->first()->getKey());
        $this->assertSame([$alpha->getKey(), $bravo->getKey()], $ordered->where('sort_order', 5)->modelKeys());
    }

    public function test_ordered_by_name_scope_sorts_alphabetically(): void
    {
        // Arrange: same sort order so only the name ordering matters.
        $delta = Channel::factory()->create(['sort_order' => 10, 'name' => 'Delta Shop']);
        $charlie = Channel::factory()->create(['sort_order' => 10, 'name' => 'Charlie Shop']);
        $echo = Channel::factory()->create(['sort_order' => 10, 'name' => 'Echo Shop']);

        // Act: fetch using the orderedByName scope in isolation.
        $ordered = Channel::orderedByName()->get();

        // Assert: collection is sorted alphabetically regardless of sort_order values.
        $this->assertSame([
            $charlie->getKey(),
            $delta->getKey(),
            $echo->getKey(),
        ], $ordered->modelKeys());
    }
}
