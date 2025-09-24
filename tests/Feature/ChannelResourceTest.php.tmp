<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class ChannelResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_channels(): void
    {
        Channel::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class)
            ->assertCanSeeTableRecords(Channel::all());
    }

    public function test_can_create_channel(): void
    {
        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\CreateChannel::class)
            ->fillForm([
                'name' => 'Test Channel',
                'slug' => 'test-channel',
                'code' => 'TEST',
                'type' => 'web',
                'description' => 'Test channel description',
                'url' => 'https://test.example.com',
                'domain' => 'test.example.com',
                'timezone' => 'UTC',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'currency_position' => 'after',
                'is_enabled' => true,
                'is_default' => false,
                'is_active' => true,
                'ssl_enabled' => true,
                'analytics_enabled' => false,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('channels', [
            'name' => 'Test Channel',
            'slug' => 'test-channel',
            'code' => 'TEST',
            'type' => 'web',
        ]);
    }

    public function test_can_edit_channel(): void
    {
        $channel = Channel::factory()->create();

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\EditChannel::class, [
            'record' => $channel->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Channel',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'name' => 'Updated Channel',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_view_channel(): void
    {
        $channel = Channel::factory()->create();

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ViewChannel::class, [
            'record' => $channel->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$channel]);
    }

    public function test_can_delete_channel(): void
    {
        $channel = Channel::factory()->create();

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class)
            ->callTableAction('delete', $channel)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('channels', [
            'id' => $channel->id,
        ]);
    }

    public function test_can_filter_channels_by_type(): void
    {
        Channel::factory()->create(['type' => 'web']);
        Channel::factory()->create(['type' => 'mobile']);

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class)
            ->filterTable('type', 'web')
            ->assertCanSeeTableRecords(Channel::where('type', 'web')->get())
            ->assertCanNotSeeTableRecords(Channel::where('type', 'mobile')->get());
    }

    public function test_can_filter_channels_by_status(): void
    {
        Channel::factory()->create(['is_enabled' => true]);
        Channel::factory()->create(['is_enabled' => false]);

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class)
            ->filterTable('is_enabled', true)
            ->assertCanSeeTableRecords(Channel::where('is_enabled', true)->get())
            ->assertCanNotSeeTableRecords(Channel::where('is_enabled', false)->get());
    }

    public function test_can_search_channels(): void
    {
        Channel::factory()->create(['name' => 'Web Channel']);
        Channel::factory()->create(['name' => 'Mobile Channel']);

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class)
            ->searchTable('Web')
            ->assertCanSeeTableRecords(Channel::where('name', 'like', '%Web%')->get())
            ->assertCanNotSeeTableRecords(Channel::where('name', 'like', '%Mobile%')->get());
    }

    public function test_channel_validation_rules(): void
    {
        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\CreateChannel::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
                'code' => '',
                'type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'slug' => 'required',
                'code' => 'required',
                'type' => 'required',
            ]);
    }

    public function test_channel_slug_auto_generation(): void
    {
        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\CreateChannel::class)
            ->fillForm([
                'name' => 'Test Channel Name',
            ])
            ->assertFormSet('slug', 'test-channel-name');
    }

    public function test_channel_unique_validation(): void
    {
        Channel::factory()->create(['slug' => 'existing-slug', 'code' => 'EXISTING']);

        Livewire::test(\App\Filament\Resources\Channels\ChannelResource\Pages\CreateChannel::class)
            ->fillForm([
                'name' => 'Test Channel',
                'slug' => 'existing-slug',
                'code' => 'EXISTING',
                'type' => 'web',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'slug' => 'unique',
                'code' => 'unique',
            ]);
    }

    public function test_channel_relationships(): void
    {
        $channel = Channel::factory()->create();
        $order = Order::factory()->create(['channel_id' => $channel->id]);
        $product = Product::factory()->create();
        $discount = Discount::factory()->create(['channel_id' => $channel->id]);

        $channel->products()->attach($product);

        $this->assertTrue($channel->orders()->exists());
        $this->assertTrue($channel->products()->exists());
        $this->assertTrue($channel->discounts()->exists());
    }

    public function test_channel_scopes(): void
    {
        Channel::factory()->create(['is_enabled' => true, 'is_active' => true]);
        Channel::factory()->create(['is_enabled' => false, 'is_active' => true]);
        Channel::factory()->create(['is_enabled' => true, 'is_active' => false]);

        $this->assertEquals(1, Channel::enabled()->count());
        $this->assertEquals(1, Channel::active()->count());
    }

    public function test_channel_default_scope(): void
    {
        Channel::factory()->create(['is_default' => true]);
        Channel::factory()->create(['is_default' => false]);

        $this->assertEquals(1, Channel::default()->count());
    }

    public function test_channel_type_scope(): void
    {
        Channel::factory()->create(['type' => 'web']);
        Channel::factory()->create(['type' => 'mobile']);

        $this->assertEquals(1, Channel::byType('web')->count());
        $this->assertEquals(1, Channel::byType('mobile')->count());
    }

    public function test_channel_ordered_scope(): void
    {
        Channel::factory()->create(['name' => 'B Channel', 'sort_order' => 2]);
        Channel::factory()->create(['name' => 'A Channel', 'sort_order' => 1]);

        $channels = Channel::ordered()->get();
        $this->assertEquals('A Channel', $channels->first()->name);
        $this->assertEquals('B Channel', $channels->last()->name);
    }
}
