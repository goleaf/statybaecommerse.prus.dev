<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->adminUser = User::factory()->create([
        'email'    => 'admin@test.com',
        'is_admin' => true,
    ]);
});

describe('Product Resource CRUD', function (): void {
    it('displays product list page', function (): void {
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products');

        $response->assertStatus(200)
            ->assertSee('Products');
    });

    it('shows product creation form', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/create');

        $response->assertStatus(200)
            ->assertSee('Create Product');
    });

    it('displays individual product details', function (): void {
        $product = Product::factory()->create([
            'name' => 'Test Product',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertSee('Test Product');
    });
});

describe('Category Resource CRUD', function (): void {
    it('displays category list page', function (): void {
        Category::factory()->count(2)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/categories');

        $response->assertStatus(200)
            ->assertSee('Categories');
    });

    it('shows category creation form', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/categories/create');

        $response->assertStatus(200)
            ->assertSee('Create Category');
    });
});

describe('Order Resource CRUD', function (): void {
    it('displays order list page', function (): void {
        $customer = Customer::factory()->create();
        Order::factory()->count(2)->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/orders');

        $response->assertStatus(200)
            ->assertSee('Orders');
    });

    it('shows order creation form', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/orders/create');

        $response->assertStatus(200)
            ->assertSee('Create Order');
    });

    it('displays individual order details', function (): void {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id'  => $customer->id,
            'order_number' => 'ORD-12345',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertSee('ORD-12345');
    });
});

describe('Customer Resource CRUD', function (): void {
    it('displays customer list page', function (): void {
        Customer::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/customers');

        $response->assertStatus(200)
            ->assertSee('Customers');
    });

    it('shows customer creation form', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/customers/create');

        $response->assertStatus(200)
            ->assertSee('Create Customer');
    });

    it('displays individual customer details', function (): void {
        $customer = Customer::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertSee('John Doe');
    });
});

describe('Brand Resource CRUD', function (): void {
    it('displays brand list page', function (): void {
        Brand::factory()->count(2)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/brands');

        $response->assertStatus(200)
            ->assertSee('Brands');
    });

    it('shows brand creation form', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/brands/create');

        $response->assertStatus(200)
            ->assertSee('Create Brand');
    });

    it('displays individual brand details', function (): void {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/brands/{$brand->id}");

        $response->assertStatus(200)
            ->assertSee('Test Brand');
    });
});

describe('Resource Authorization', function (): void {
    it('prevents unauthorized access to admin resources', function (): void {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $protectedRoutes = [
            '/admin/products',
            '/admin/categories',
            '/admin/orders',
            '/admin/customers',
            '/admin/brands',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->actingAs($regularUser)->get($route);

            // Should redirect to login or show 403
            expect($response->getStatusCode())->toBeIn([302, 403]);
        }
    });

    it('allows admin access to all resources', function (): void {
        $adminRoutes = [
            '/admin/products',
            '/admin/categories',
            '/admin/orders',
            '/admin/customers',
            '/admin/brands',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $response->assertStatus(200);
        }
    });
});

describe('Resource Performance', function (): void {
    it('loads resource lists efficiently with pagination', function (): void {
        // Create many records to test pagination
        Product::factory()->count(50)->create();

        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should load within reasonable time (2 seconds)
        expect($executionTime)->toBeLessThan(2.0);
    });

    it('handles empty resource lists gracefully', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products');

        $response->assertStatus(200)
            ->assertSee('No products found', false);
    });
});
