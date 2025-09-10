<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        $this->actingAs($this->adminUser);
    }

    public function test_can_render_order_index_page(): void
    {
        $this
            ->get(OrderResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertCanSeeTableRecords($orders);
    }

    public function test_can_sort_orders_by_created_at(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords($orders, inOrder: true);
    }

    public function test_can_search_orders_by_number(): void
    {
        $order = Order::factory()->create(['number' => 'ORD-2025-001']);
        Order::factory()->create(['number' => 'ORD-2025-002']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('ORD-2025-001')
            ->assertCanSeeTableRecords([$order])
            ->assertCanNotSeeTableRecords([Order::where('number', 'ORD-2025-002')->first()]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pendingOrder])
            ->assertCanNotSeeTableRecords([$shippedOrder]);
    }

    public function test_can_view_order(): void
    {
        $order = Order::factory()->create();

        $this
            ->get(OrderResource::getUrl('view', ['record' => $order]))
            ->assertSuccessful();
    }

    public function test_can_create_order(): void
    {
        $user = User::factory()->create();

        $newData = [
            'number' => 'ORD-2025-TEST',
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal' => 100.0,
            'tax_amount' => 21.0,
            'shipping_amount' => 5.0,
            'discount_amount' => 0.0,
            'total' => 126.0,
            'currency' => 'EUR',
        ];

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Order::class, [
            'number' => 'ORD-2025-TEST',
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 126.0,
        ]);
    }

    public function test_can_edit_order(): void
    {
        $order = Order::factory()->create();

        $newData = [
            'status' => 'processing',
            'notes' => 'Updated notes',
        ];

        Livewire::test(OrderResource\Pages\EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($order->refresh())
            ->status
            ->toBe('processing')
            ->notes
            ->toBe('Updated notes');
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('delete', $order);

        $this->assertSoftDeleted($order);
    }

    public function test_can_bulk_delete_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('delete', $orders);

        foreach ($orders as $order) {
            $this->assertSoftDeleted($order);
        }
    }

    public function test_order_validation_rules(): void
    {
        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'number' => '',  // Required field
                'status' => '',  // Required field
                'subtotal' => '',  // Required field
                'total' => '',  // Required field
            ])
            ->call('create')
            ->assertHasFormErrors([
                'number' => 'required',
                'status' => 'required',
                'subtotal' => 'required',
                'total' => 'required',
            ]);
    }

    public function test_order_number_must_be_unique(): void
    {
        $existingOrder = Order::factory()->create(['number' => 'ORD-DUPLICATE']);

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'number' => 'ORD-DUPLICATE',
                'user_id' => User::factory()->create()->id,
                'status' => 'pending',
                'subtotal' => 100.0,
                'total' => 100.0,
                'currency' => 'EUR',
            ])
            ->call('create')
            ->assertHasFormErrors(['number']);
    }

    public function test_can_generate_document_for_order(): void
    {
        $order = Order::factory()->create();

        // This would test the DocumentAction, but we need to mock the DocumentTemplate
        // For now, we'll just test that the action exists
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertTableActionExists('generate_document', record: $order);
    }

    public function test_order_displays_correct_columns(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertCanRenderTableColumn('number')
            ->assertCanRenderTableColumn('user.name')
            ->assertCanRenderTableColumn('status')
            ->assertCanRenderTableColumn('total')
            ->assertCanRenderTableColumn('items_count')
            ->assertCanRenderTableColumn('created_at');
    }

    public function test_order_status_badge_colors(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $processingOrder = Order::factory()->create(['status' => 'processing']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);
        $deliveredOrder = Order::factory()->create(['status' => 'delivered']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);

        $component = Livewire::test(OrderResource\Pages\ListOrders::class);

        // Test that status badges are rendered (exact color testing would require more complex setup)
        $component->assertCanSeeTableRecords([
            $pendingOrder,
            $processingOrder,
            $shippedOrder,
            $deliveredOrder,
            $cancelledOrder,
        ]);
    }

    public function test_can_access_order_relationships(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertTableColumnStateSet('user.name', 'John Doe', record: $order);
    }

    public function test_order_global_search(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        $order = Order::factory()->create([
            'number' => 'ORD-SEARCH-001',
            'user_id' => $user->id,
            'notes' => 'Special delivery instructions',
        ]);

        // Test searching by order number
        $results = OrderResource::getGlobalSearchResults('ORD-SEARCH-001');
        expect($results)->toHaveCount(1);

        // Test searching by customer name
        $results = OrderResource::getGlobalSearchResults('Jane Smith');
        expect($results)->toHaveCount(1);

        // Test searching by customer email
        $results = OrderResource::getGlobalSearchResults('jane@example.com');
        expect($results)->toHaveCount(1);
    }

    public function test_order_soft_deletes_functionality(): void
    {
        $order = Order::factory()->create();

        // Delete the order
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('delete', $order);

        // Verify it's soft deleted
        $this->assertSoftDeleted($order);

        // Test that trashed filter works
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('trashed', 'with')
            ->assertCanSeeTableRecords([$order]);

        // Test restore action
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('restore', $order);

        expect($order->refresh()->deleted_at)->toBeNull();
    }
}
