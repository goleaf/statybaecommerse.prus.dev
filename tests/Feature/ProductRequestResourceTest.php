<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->adminUser);
    }

    private function makePublishedProduct(): Product
    {
        return Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_can_list_product_requests(): void
    {
        $product = $this->makePublishedProduct();

        ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'name' => 'Test Request',
            'email' => 'test@example.com',
            'status' => 'pending',
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ListProductRequests::class)
            ->assertCanSeeTableRecords(ProductRequest::all());
    }

    public function test_can_create_product_request(): void
    {
        $product = $this->makePublishedProduct();

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\CreateProductRequest::class)
            ->fillForm([
                'product_id' => $product->id,
                'user_id' => $this->adminUser->id,
                'name' => 'New Request',
                'email' => 'new@example.com',
                'phone' => '+37012345678',
                'message' => 'Test message',
                'requested_quantity' => 2,
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_requests', [
            'name' => 'New Request',
            'email' => 'new@example.com',
            'status' => 'pending',
            'user_id' => $this->adminUser->id,
        ]);
    }

    public function test_can_edit_product_request(): void
    {
        $product = $this->makePublishedProduct();

        $request = ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'status' => 'pending',
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\EditProductRequest::class, [
            'record' => $request->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'completed',
                'admin_notes' => 'Request completed successfully',
                'phone' => '+37012345678',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_requests', [
            'id' => $request->id,
            'status' => 'completed',
            'admin_notes' => 'Request completed successfully',
        ]);
    }

    public function test_can_view_product_request(): void
    {
        $product = $this->makePublishedProduct();

        $request = ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'name' => 'Test Request',
            'email' => 'test@example.com',
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ViewProductRequest::class, [
            'record' => $request->getRouteKey(),
        ])
            ->assertSet('record.id', $request->id);
    }

    public function test_can_filter_by_status(): void
    {
        $product = $this->makePublishedProduct();

        ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'status' => 'pending',
        ]);

        ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'status' => 'completed',
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ListProductRequests::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords(ProductRequest::where('status', 'pending')->get())
            ->assertCanNotSeeTableRecords(ProductRequest::where('status', 'completed')->get());
    }

    public function test_can_filter_by_product(): void
    {
        $product1 = $this->makePublishedProduct();
        $product2 = $this->makePublishedProduct();

        ProductRequest::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $this->adminUser->id,
        ]);

        ProductRequest::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $this->adminUser->id,
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ListProductRequests::class)
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords(ProductRequest::where('product_id', $product1->id)->get())
            ->assertCanNotSeeTableRecords(ProductRequest::where('product_id', $product2->id)->get());
    }

    public function test_can_search_product_requests(): void
    {
        $product = $this->makePublishedProduct();

        ProductRequest::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
            'name' => 'Test Request',
            'email' => 'test@example.com',
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ListProductRequests::class)
            ->searchTable('Test Request')
            ->assertCanSeeTableRecords(ProductRequest::where('name', 'like', '%Test Request%')->get());
    }

    public function test_can_bulk_delete_product_requests(): void
    {
        $product = $this->makePublishedProduct();

        $requests = ProductRequest::factory()->count(3)->create([
            'product_id' => $product->id,
            'user_id' => $this->adminUser->id,
        ]);

        Livewire::test(\App\Filament\Resources\ProductRequestResource\Pages\ListProductRequests::class)
            ->callTableBulkAction('delete', $requests->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        $requests->each(function (ProductRequest $request): void {
            $this->assertSoftDeleted('product_requests', ['id' => $request->id]);
        });
    }

    public function test_product_request_relationships_work(): void
    {
        $product = $this->makePublishedProduct()->forceFill(['name' => 'Test Product']);
        $product->save();
        $user = User::factory()->create(['name' => 'Test User']);

        $request = ProductRequest::factory()->for($product)->for($user)->create();

        $this->assertEquals('Test Product', $request->product->name);
        $this->assertEquals('Test User', $request->user->name);
    }

    public function test_product_request_status_scopes_work(): void
    {
        ProductRequest::factory()->pending()->create([
            'user_id' => $this->adminUser->id,
        ]);
        ProductRequest::factory()->completed()->create([
            'user_id' => $this->adminUser->id,
        ]);

        $this->assertCount(1, ProductRequest::pending()->get());
        $this->assertCount(1, ProductRequest::completed()->get());
    }
}
