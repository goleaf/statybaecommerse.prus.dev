<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_request_can_be_created(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        
        $request = ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I would like to request this product',
            'requested_quantity' => 5,
            'status' => 'pending',
            'admin_notes' => 'Customer inquiry',
        ]);

        $this->assertInstanceOf(ProductRequest::class, $request);
        $this->assertEquals($product->id, $request->product_id);
        $this->assertEquals($user->id, $request->user_id);
        $this->assertEquals('John Doe', $request->name);
        $this->assertEquals('john@example.com', $request->email);
        $this->assertEquals('+1234567890', $request->phone);
        $this->assertEquals('I would like to request this product', $request->message);
        $this->assertEquals(5, $request->requested_quantity);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals('Customer inquiry', $request->admin_notes);
    }

    public function test_product_request_fillable_attributes(): void
    {
        $request = new ProductRequest();
        $fillable = $request->getFillable();

        $expectedFillable = [
            'product_id',
            'user_id',
            'name',
            'email',
            'phone',
            'message',
            'requested_quantity',
            'status',
            'admin_notes',
            'responded_at',
            'responded_by',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_product_request_casts(): void
    {
        $request = ProductRequest::factory()->create([
            'requested_quantity' => '10',
            'responded_at' => '2024-01-01 12:00:00',
        ]);

        $this->assertIsInt($request->requested_quantity);
        $this->assertEquals(10, $request->requested_quantity);
        $this->assertInstanceOf(\Carbon\Carbon::class, $request->responded_at);
    }

    public function test_product_request_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $request = ProductRequest::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $request->product);
        $this->assertEquals($product->id, $request->product->id);
    }

    public function test_product_request_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $request = ProductRequest::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $request->user);
        $this->assertEquals($user->id, $request->user->id);
    }

    public function test_product_request_belongs_to_responder(): void
    {
        $responder = User::factory()->create();
        $request = ProductRequest::factory()->create(['responded_by' => $responder->id]);

        $this->assertInstanceOf(User::class, $request->respondedBy);
        $this->assertEquals($responder->id, $request->respondedBy->id);
    }

    public function test_product_request_scopes(): void
    {
        $pendingRequest = ProductRequest::factory()->create(['status' => 'pending']);
        $completedRequest = ProductRequest::factory()->create(['status' => 'completed']);
        $cancelledRequest = ProductRequest::factory()->create(['status' => 'cancelled']);

        // Test pending scope
        $pendingRequests = ProductRequest::pending()->get();
        $this->assertTrue($pendingRequests->contains($pendingRequest));
        $this->assertFalse($pendingRequests->contains($completedRequest));
        $this->assertFalse($pendingRequests->contains($cancelledRequest));

        // Test completed scope
        $completedRequests = ProductRequest::completed()->get();
        $this->assertFalse($completedRequests->contains($pendingRequest));
        $this->assertTrue($completedRequests->contains($completedRequest));
        $this->assertFalse($completedRequests->contains($cancelledRequest));

        // Test cancelled scope
        $cancelledRequests = ProductRequest::cancelled()->get();
        $this->assertFalse($cancelledRequests->contains($pendingRequest));
        $this->assertFalse($cancelledRequests->contains($completedRequest));
        $this->assertTrue($cancelledRequests->contains($cancelledRequest));
    }

    public function test_product_request_scope_by_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $request1 = ProductRequest::factory()->create(['product_id' => $product1->id]);
        $request2 = ProductRequest::factory()->create(['product_id' => $product2->id]);

        $product1Requests = ProductRequest::byProduct($product1->id)->get();
        $this->assertTrue($product1Requests->contains($request1));
        $this->assertFalse($product1Requests->contains($request2));
    }

    public function test_product_request_scope_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $request1 = ProductRequest::factory()->create(['user_id' => $user1->id]);
        $request2 = ProductRequest::factory()->create(['user_id' => $user2->id]);

        $user1Requests = ProductRequest::byUser($user1->id)->get();
        $this->assertTrue($user1Requests->contains($request1));
        $this->assertFalse($user1Requests->contains($request2));
    }

    public function test_product_request_scope_by_status(): void
    {
        $pendingRequest = ProductRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = ProductRequest::factory()->create(['status' => 'approved']);

        $pendingRequests = ProductRequest::byStatus('pending')->get();
        $this->assertTrue($pendingRequests->contains($pendingRequest));
        $this->assertFalse($pendingRequests->contains($approvedRequest));
    }

    public function test_product_request_scope_responded(): void
    {
        $respondedRequest = ProductRequest::factory()->create([
            'responded_at' => now(),
            'responded_by' => User::factory()->create()->id,
        ]);
        
        $unrespondedRequest = ProductRequest::factory()->create([
            'responded_at' => null,
            'responded_by' => null,
        ]);

        $respondedRequests = ProductRequest::responded()->get();
        $this->assertTrue($respondedRequests->contains($respondedRequest));
        $this->assertFalse($respondedRequests->contains($unrespondedRequest));
    }

    public function test_product_request_scope_unresponded(): void
    {
        $respondedRequest = ProductRequest::factory()->create([
            'responded_at' => now(),
            'responded_by' => User::factory()->create()->id,
        ]);
        
        $unrespondedRequest = ProductRequest::factory()->create([
            'responded_at' => null,
            'responded_by' => null,
        ]);

        $unrespondedRequests = ProductRequest::unresponded()->get();
        $this->assertFalse($unrespondedRequests->contains($respondedRequest));
        $this->assertTrue($unrespondedRequests->contains($unrespondedRequest));
    }

    public function test_product_request_scope_recent(): void
    {
        $recentRequest = ProductRequest::factory()->create(['created_at' => now()]);
        $oldRequest = ProductRequest::factory()->create(['created_at' => now()->subDays(10)]);

        $recentRequests = ProductRequest::recent()->get();
        $this->assertTrue($recentRequests->contains($recentRequest));
        $this->assertFalse($recentRequests->contains($oldRequest));
    }

    public function test_product_request_status_methods(): void
    {
        $pendingRequest = ProductRequest::factory()->create(['status' => 'pending']);
        $completedRequest = ProductRequest::factory()->create(['status' => 'completed']);
        $cancelledRequest = ProductRequest::factory()->create(['status' => 'cancelled']);

        $this->assertTrue($pendingRequest->isPending());
        $this->assertFalse($pendingRequest->isCompleted());
        $this->assertFalse($pendingRequest->isCancelled());

        $this->assertFalse($completedRequest->isPending());
        $this->assertTrue($completedRequest->isCompleted());
        $this->assertFalse($completedRequest->isCancelled());

        $this->assertFalse($cancelledRequest->isPending());
        $this->assertFalse($cancelledRequest->isCompleted());
        $this->assertTrue($cancelledRequest->isCancelled());
    }

    public function test_product_request_is_responded_method(): void
    {
        $respondedRequest = ProductRequest::factory()->create([
            'responded_at' => now(),
            'responded_by' => User::factory()->create()->id,
        ]);
        
        $unrespondedRequest = ProductRequest::factory()->create([
            'responded_at' => null,
            'responded_by' => null,
        ]);

        $this->assertTrue($respondedRequest->isResponded());
        $this->assertFalse($unrespondedRequest->isResponded());
    }

    public function test_product_request_mark_as_responded_method(): void
    {
        $user = User::factory()->create();
        $request = ProductRequest::factory()->create([
            'responded_at' => null,
            'responded_by' => null,
        ]);

        $request->markAsResponded($user->id);

        $this->assertNotNull($request->responded_at);
        $this->assertEquals($user->id, $request->responded_by);
        $this->assertTrue($request->isResponded());
    }

    public function test_product_request_uses_soft_deletes(): void
    {
        $request = ProductRequest::factory()->create();
        $requestId = $request->id;

        $request->delete();

        $this->assertSoftDeleted('product_requests', ['id' => $requestId]);
        $this->assertNull(ProductRequest::find($requestId));
        $this->assertNotNull(ProductRequest::withTrashed()->find($requestId));
    }

    public function test_product_request_uses_activity_log(): void
    {
        $request = new ProductRequest();
        
        $this->assertTrue(method_exists($request, 'getActivitylogOptions'));
    }

    public function test_product_request_table_name(): void
    {
        $request = new ProductRequest();
        $this->assertEquals('product_requests', $request->getTable());
    }

    public function test_product_request_factory(): void
    {
        $request = ProductRequest::factory()->create();

        $this->assertInstanceOf(ProductRequest::class, $request);
        $this->assertNotEmpty($request->product_id);
        $this->assertNotEmpty($request->user_id);
        $this->assertNotEmpty($request->name);
        $this->assertNotEmpty($request->email);
        $this->assertNotEmpty($request->status);
    }
}
