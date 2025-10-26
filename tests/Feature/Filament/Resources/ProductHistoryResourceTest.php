<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductHistoryResource\Pages\CreateProductHistory;
use App\Filament\Resources\ProductHistoryResource\Pages\EditProductHistory;
use App\Filament\Resources\ProductHistoryResource\Pages\ListProductHistories;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductHistoryResourceTest extends TestCase
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

    public function test_list_page_displays_product_history(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id'    => $this->admin->id,
            'action'     => 'created',
            'field_name' => 'name',
        ]);

        Livewire::test(ListProductHistories::class)
            ->call('loadTable')
            ->assertSee('name')
            ->searchTable('name')
            ->assertSee('name');
    }

    public function test_can_create_product_history_entry(): void
    {
        Livewire::test(CreateProductHistory::class)
            ->fillForm([
                'product_id' => $this->product->id,
                'user_id'    => $this->admin->id,
                'action'     => 'updated',
                'field_name' => 'price',
                'old_value'  => '99.99',
                'new_value'  => '129.99',
                'meta'       => ['reason' => 'Seasonal pricing'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'product_id' => $this->product->id,
            'user_id'    => $this->admin->id,
            'action'     => 'updated',
            'field_name' => 'price',
        ]);
    }

    public function test_can_edit_product_history_entry(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id'    => $this->admin->id,
            'action'     => 'updated',
            'field_name' => 'description',
        ]);

        Livewire::test(EditProductHistory::class, ['record' => $history->getRouteKey()])
            ->fillForm([
                'product_id' => $this->product->id,
                'user_id'    => $this->admin->id,
                'action'     => 'status_changed',
                'new_value'  => 'published',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'id'        => $history->id,
            'action'    => 'status_changed',
            'new_value' => '"published"',
        ]);
    }

    public function test_can_filter_history_by_product_and_user(): void
    {
        $matching = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id'    => $this->admin->id,
            'action'     => 'updated',
        ]);

        $other = ProductHistory::factory()->create();

        Livewire::test(ListProductHistories::class)
            ->call('loadTable')
            ->filterTable('product_id', $this->product->id)
            ->filterTable('user_id', $this->admin->id)
            ->assertSee($matching->field_name ?? '')
            ->assertDontSee($other->field_name ?? '');
    }

    public function test_can_filter_history_by_date_range(): void
    {
        $today = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => Carbon::now(),
        ]);

        ProductHistory::factory()->create([
            'created_at' => Carbon::now()->subDays(10),
        ]);

        Livewire::test(ListProductHistories::class)
            ->filterTable('date', [
                'from'  => Carbon::now()->subDay()->toDateString(),
                'until' => Carbon::now()->addDay()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$today]);
    }
}
