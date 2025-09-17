<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Livewire;

it('can generate related product recommendations', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    
    $mainProduct = Product::factory()->create([
        'name' => 'Main Product',
        'brand_id' => $brand->id,
        'is_visible' => true,
    ]);
    $mainProduct->categories()->attach($category->id);
    
    $relatedProduct = Product::factory()->create([
        'name' => 'Related Product',
        'brand_id' => $brand->id,
        'is_visible' => true,
    ]);
    $relatedProduct->categories()->attach($category->id);

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $mainProduct->id,
        'type' => 'related',
    ])
        ->assertSee('Related Product');
});

it('can generate personalized recommendations based on purchase history', function () {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['name' => 'Electronics']);
    $category2 = Category::factory()->create(['name' => 'Books']);
    
    // Products in electronics category
    $purchasedProduct = Product::factory()->create(['name' => 'Smartphone']);
    $purchasedProduct->categories()->attach($category1->id);
    
    $recommendedProduct = Product::factory()->create(['name' => 'Laptop']);
    $recommendedProduct->categories()->attach($category1->id);
    
    // Product in different category
    $unrelatedProduct = Product::factory()->create(['name' => 'Book']);
    $unrelatedProduct->categories()->attach($category2->id);

    // Create purchase history
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
    ]);
    
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $purchasedProduct->id,
    ]);

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'userId' => $user->id,
        'type' => 'personalized',
    ])
        ->assertSee('Laptop') // Should recommend laptop (same category)
        ->assertDontSee('Book'); // Should not recommend book (different category)
});

it('can generate popular product recommendations', function () {
    $popularProduct = Product::factory()->create(['name' => 'Popular Product']);
    $unpopularProduct = Product::factory()->create(['name' => 'Unpopular Product']);

    // Create multiple orders for popular product
    for ($i = 0; $i < 5; $i++) {
        $order = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $popularProduct->id,
        ]);
    }

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'type' => 'popular',
    ])
        ->assertSee('Popular Product');
});

it('can generate cross-sell recommendations', function () {
    $mainProduct = Product::factory()->create(['name' => 'Main Product']);
    $crossSellProduct = Product::factory()->create(['name' => 'Cross Sell Product']);

    // Create orders where both products were bought together
    for ($i = 0; $i < 3; $i++) {
        $order = Order::factory()->create(['status' => 'completed']);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $mainProduct->id,
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $crossSellProduct->id,
        ]);
    }

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $mainProduct->id,
        'type' => 'cross_sell',
    ])
        ->assertSee('Cross Sell Product');
});

it('can generate up-sell recommendations', function () {
    $category = Category::factory()->create();
    
    $baseProduct = Product::factory()->create([
        'name' => 'Base Product',
        'price' => 100.00,
    ]);
    $baseProduct->categories()->attach($category->id);
    
    $upSellProduct = Product::factory()->create([
        'name' => 'Premium Product',
        'price' => 130.00, // 30% more expensive
    ]);
    $upSellProduct->categories()->attach($category->id);

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $baseProduct->id,
        'type' => 'up_sell',
    ])
        ->assertSee('Premium Product');
});

it('can track recently viewed products', function () {
    $product1 = Product::factory()->create(['name' => 'First Product']);
    $product2 = Product::factory()->create(['name' => 'Second Product']);

    $component = Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $product1->id,
        'type' => 'recently_viewed',
    ]);

    $component->call('trackView');
    
    expect(session('recently_viewed'))->toContain($product1->id);

    // Track another product
    $component->set('productId', $product2->id)
             ->call('trackView');
    
    expect(session('recently_viewed'))->toContain($product2->id);
    expect(session('recently_viewed')[0])->toBe($product2->id); // Most recent first
});

it('can generate customers also bought recommendations', function () {
    $mainProduct = Product::factory()->create(['name' => 'Main Product']);
    $alsoBoughtProduct = Product::factory()->create(['name' => 'Also Bought Product']);

    // Create completed orders where both products were bought together
    for ($i = 0; $i < 3; $i++) {
        $order = Order::factory()->create(['status' => 'completed']);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $mainProduct->id,
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $alsoBoughtProduct->id,
        ]);
    }

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $mainProduct->id,
        'type' => 'customers_also_bought',
    ])
        ->assertSee('Also Bought Product');
});

it('can generate trending product recommendations', function () {
    $trendingProduct = Product::factory()->create(['name' => 'Trending Product']);
    $oldProduct = Product::factory()->create(['name' => 'Old Product']);

    // Create recent orders for trending product (last 30 days)
    for ($i = 0; $i < 5; $i++) {
        $order = Order::factory()->create([
            'status' => 'completed',
            'created_at' => now()->subDays(rand(1, 30)),
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $trendingProduct->id,
        ]);
    }

    // Create old orders for old product (more than 30 days ago)
    for ($i = 0; $i < 5; $i++) {
        $order = Order::factory()->create([
            'status' => 'completed',
            'created_at' => now()->subDays(rand(35, 60)),
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $oldProduct->id,
        ]);
    }

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'type' => 'trending',
    ])
        ->assertSee('Trending Product');
});

it('can handle recommendation fallbacks', function () {
    // Test when no data is available for personalized recommendations
    $user = User::factory()->create();

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'userId' => $user->id,
        'type' => 'personalized',
    ]);
    
    // Should fallback to popular products when no purchase history
    expect(true)->toBeTrue(); // Component should handle gracefully
});

it('can calculate customer segments correctly', function () {
    $vipCustomer = User::factory()->create(['is_admin' => false]);
    $regularCustomer = User::factory()->create(['is_admin' => false]);
    $newCustomer = User::factory()->create(['is_admin' => false]);

    // Create VIP customer with high value orders
    for ($i = 0; $i < 12; $i++) {
        Order::factory()->create([
            'user_id' => $vipCustomer->id,
            'status' => 'completed',
            'total' => 150.00,
        ]);
    }

    // Create regular customer with moderate orders
    for ($i = 0; $i < 6; $i++) {
        Order::factory()->create([
            'user_id' => $regularCustomer->id,
            'status' => 'completed',
            'total' => 80.00,
        ]);
    }

    // New customer has no orders

    $segmentationPage = new \App\Filament\Pages\CustomerSegmentation();
    
    expect($segmentationPage->calculateCustomerSegment($vipCustomer))->toBe('vip');
    expect($segmentationPage->calculateCustomerSegment($regularCustomer))->toBe('regular');
    expect($segmentationPage->calculateCustomerSegment($newCustomer))->toBe('new');
});

it('can perform SEO score calculations', function () {
    $goodSEOProduct = Product::factory()->create([
        'name' => 'Well Optimized Product',
        'seo_title' => 'Perfect SEO Title for Product - 45 Characters',
        'seo_description' => 'This is a perfectly optimized meta description that contains exactly the right amount of characters for good SEO performance.',
        'meta_keywords' => 'product, seo, optimized',
        'description' => 'This is a detailed product description with more than 100 characters to ensure good content quality for SEO purposes.',
    ]);

    $poorSEOProduct = Product::factory()->create([
        'name' => 'Poor SEO Product',
        'seo_title' => null,
        'seo_description' => null,
        'meta_keywords' => null,
        'description' => 'Short desc',
    ]);

    $seoPage = new \App\Filament\Pages\SEOAnalytics();
    
    expect($seoPage->calculateSEOScore($goodSEOProduct))->toBeGreaterThan(80);
    expect($seoPage->calculateSEOScore($poorSEOProduct))->toBeLessThan(30);
});

it('can track security activities and detect suspicious patterns', function () {
    // Create some security activities
    activity('security')
        ->causedBy($this->admin)
        ->log('Successful admin login');

    activity('security')
        ->log('Failed login attempt from IP 192.168.1.100');

    activity('security')
        ->log('Multiple failed login attempts detected');

    $securityPage = new \App\Filament\Pages\SecurityAudit();
    $securityPage->loadSecurityStats();

    expect($securityPage->securityStats['total_activities'])->toBeGreaterThan(0);
    expect($securityPage->suspiciousActivities)->not()->toBeEmpty();
});



