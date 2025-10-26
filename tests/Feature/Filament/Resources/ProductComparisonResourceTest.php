<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductComparisonResource\Pages\CreateProductComparison;
use App\Filament\Resources\ProductComparisonResource\Pages\EditProductComparison;
use App\Filament\Resources\ProductComparisonResource\Pages\ListProductComparisons;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductComparisonResourceTest extends TestCase
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

    public function test_list_page_displays_product_comparisons(): void
    {
        $comparison = ProductComparison::factory()->create([
            'user_id'    => $this->admin->id,
            'product_id' => $this->product->id,
            'session_id' => 'session-visible',
        ]);

        Livewire::test(ListProductComparisons::class)
            ->call('loadTable')
            ->assertSee('session-visible')
            ->searchTable('session-visible')
            ->assertSee('session-visible');
    }

    public function test_can_create_product_comparison(): void
    {
        Livewire::test(CreateProductComparison::class)
            ->fillForm([
                'user_id'    => $this->admin->id,
                'product_id' => $this->product->id,
                'session_id' => 'session-create',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', [
            'user_id'    => $this->admin->id,
            'product_id' => $this->product->id,
            'session_id' => 'session-create',
        ]);
    }

    public function test_can_edit_product_comparison(): void
    {
        $comparison = ProductComparison::factory()->create([
            'user_id'    => $this->admin->id,
            'product_id' => $this->product->id,
            'session_id' => 'session-original',
        ]);

        Livewire::test(EditProductComparison::class, ['record' => $comparison->getRouteKey()])
            ->fillForm([
                'user_id'    => $this->admin->id,
                'product_id' => $this->product->id,
                'session_id' => 'session-updated',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', [
            'id'         => $comparison->id,
            'session_id' => 'session-updated',
        ]);
    }

    public function test_can_delete_product_comparison_from_table(): void
    {
        $comparisons = collect([
            ProductComparison::factory()->create([
                'user_id'    => $this->admin->id,
                'product_id' => $this->product->id,
            ]),
            ProductComparison::factory()->create([
                'user_id'    => User::factory()->create()->id,
                'product_id' => $this->product->id,
            ]),
        ]);

        Livewire::test(ListProductComparisons::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $comparisons->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($comparisons as $comparison) {
            $this->assertDatabaseMissing('product_comparisons', ['id' => $comparison->id]);
        }
    }

    public function test_can_filter_product_comparisons_by_user_and_product(): void
    {
        $otherUser = User::factory()->create();
        $otherProduct = Product::factory()->create([
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        $matching = ProductComparison::factory()->create([
            'user_id'    => $this->admin->id,
            'product_id' => $this->product->id,
            'session_id' => 'match-session',
        ]);

        $other = ProductComparison::factory()->create([
            'user_id'    => $otherUser->id,
            'product_id' => $otherProduct->id,
            'session_id' => 'other-session',
        ]);

        Livewire::test(ListProductComparisons::class)
            ->call('loadTable')
            ->filterTable('user_id', $this->admin->id)
            ->filterTable('product_id', $this->product->id)
            ->assertSee('match-session')
            ->assertDontSee('other-session');
    }

    public function test_can_bulk_delete_product_comparisons(): void
    {
        $comparisons = collect([
            ProductComparison::factory()->create([
                'user_id'    => $this->admin->id,
                'product_id' => $this->product->id,
            ]),
            ProductComparison::factory()->create([
                'user_id'    => User::factory()->create()->id,
                'product_id' => $this->product->id,
            ]),
            ProductComparison::factory()->create([
                'user_id'    => User::factory()->create()->id,
                'product_id' => Product::factory()->create([
                    'status'       => 'published',
                    'published_at' => now(),
                    'is_visible'   => true,
                ])->id,
            ]),
        ]);

        Livewire::test(ListProductComparisons::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $comparisons->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($comparisons as $comparison) {
            $this->assertDatabaseMissing('product_comparisons', ['id' => $comparison->id]);
        }
    }
}
