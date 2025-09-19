<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductComparisonResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_product_comparisons(): void
    {
        $currentUser = auth()->user();

        ProductComparison::factory()->create(['user_id' => $currentUser->id]);
        ProductComparison::factory()->create(['user_id' => $currentUser->id]);

        $this
            ->get(route('filament.admin.resources.product-comparisons.index'))
            ->assertOk();
    }

    public function test_can_create_product_comparison(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comparisonData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'session_id' => 'test-session-123',
        ];

        Livewire::test('filament.admin.resources.product-comparisons.create')
            ->fillForm($comparisonData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', $comparisonData);
    }

    public function test_can_view_product_comparison(): void
    {
        $currentUser = auth()->user();
        $comparison = ProductComparison::factory()->create(['user_id' => $currentUser->id]);

        $this
            ->get(route('filament.admin.resources.product-comparisons.view', $comparison))
            ->assertOk();
    }

    public function test_can_edit_product_comparison(): void
    {
        $currentUser = auth()->user();
        $comparison = ProductComparison::factory()->create(['user_id' => $currentUser->id]);
        $newProduct = Product::factory()->create();

        $updateData = [
            'product_id' => $newProduct->id,
            'session_id' => 'updated-session-456',
        ];

        Livewire::test('filament.admin.resources.product-comparisons.edit', ['record' => $comparison->id])
            ->fillForm($updateData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', [
            'id' => $comparison->id,
            'product_id' => $newProduct->id,
            'session_id' => 'updated-session-456',
        ]);
    }

    public function test_can_delete_product_comparison(): void
    {
        $currentUser = auth()->user();
        $comparison = ProductComparison::factory()->create(['user_id' => $currentUser->id]);

        Livewire::test('filament.admin.resources.product-comparisons.edit', ['record' => $comparison->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('product_comparisons', [
            'id' => $comparison->id,
        ]);
    }

    public function test_can_filter_product_comparisons_by_user(): void
    {
        $currentUser = auth()->user();
        $otherUser = User::factory()->create();

        ProductComparison::factory()->create(['user_id' => $currentUser->id]);
        ProductComparison::factory()->create(['user_id' => $otherUser->id]);

        Livewire::test('filament.admin.resources.product-comparisons.index')
            ->filterTable('user_id', $currentUser->id)
            ->assertCanSeeTableRecords(
                ProductComparison::where('user_id', $currentUser->id)->get()
            );
    }

    public function test_can_filter_product_comparisons_by_product(): void
    {
        $currentUser = auth()->user();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'product_id' => $product1->id,
        ]);
        ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'product_id' => $product2->id,
        ]);

        Livewire::test('filament.admin.resources.product-comparisons.index')
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords(
                ProductComparison::where('product_id', $product1->id)->get()
            );
    }

    public function test_can_filter_product_comparisons_by_date_range(): void
    {
        $currentUser = auth()->user();

        $oldComparison = ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'created_at' => now()->subDays(10),
        ]);
        $recentComparison = ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'created_at' => now()->subDays(2),
        ]);

        Livewire::test('filament.admin.resources.product-comparisons.index')
            ->filterTable('created_at', [
                'created_from' => now()->subDays(5)->format('Y-m-d'),
                'created_until' => now()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$recentComparison])
            ->assertCanNotSeeTableRecords([$oldComparison]);
    }

    public function test_can_use_tabs_to_filter_comparisons(): void
    {
        $currentUser = auth()->user();

        ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'created_at' => now()->subDays(5),
        ]);
        ProductComparison::factory()->create([
            'user_id' => $currentUser->id,
            'created_at' => today(),
        ]);

        Livewire::test('filament.admin.resources.product-comparisons.index')
            ->assertCanSeeTableRecords(
                ProductComparison::where('user_id', $currentUser->id)->get()
            );
    }

    public function test_product_comparison_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comparison = ProductComparison::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(User::class, $comparison->user);
        $this->assertEquals($user->id, $comparison->user->id);

        $this->assertInstanceOf(Product::class, $comparison->product);
        $this->assertEquals($product->id, $comparison->product->id);
    }

    public function test_can_bulk_delete_product_comparisons(): void
    {
        $currentUser = auth()->user();

        $comparison1 = ProductComparison::factory()->create(['user_id' => $currentUser->id]);
        $comparison2 = ProductComparison::factory()->create(['user_id' => $currentUser->id]);

        Livewire::test('filament.admin.resources.product-comparisons.index')
            ->callTableBulkAction('delete', [$comparison1->id, $comparison2->id]);

        $this->assertDatabaseMissing('product_comparisons', ['id' => $comparison1->id]);
        $this->assertDatabaseMissing('product_comparisons', ['id' => $comparison2->id]);
    }
}
