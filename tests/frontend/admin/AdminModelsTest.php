<?php

declare(strict_types=1);

use App\Models\AnalyticsEvent;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Discount;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PartnerTier;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign Model', function (): void {
    it('can create a campaign', function (): void {
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'status' => 'active',
        ]);

        expect($campaign->name)->toBe('Test Campaign');
        expect($campaign->slug)->toBe('test-campaign');
        expect($campaign->status)->toBe('active');
    });

    it('has correct fillable attributes', function (): void {
        $campaign = new Campaign;
        $fillable = $campaign->getFillable();

        expect($fillable)->toContain('name');
        expect($fillable)->toContain('slug');
        expect($fillable)->toContain('status');
        expect($fillable)->toContain('description');
    });

    it('can scope active campaigns', function (): void {
        Campaign::factory()->create(['status' => 'active']);
        Campaign::factory()->create(['status' => 'draft']);
        Campaign::factory()->create(['status' => 'inactive']);

        $activeCampaigns = Campaign::active()->get();

        expect($activeCampaigns)->toHaveCount(1);
        expect($activeCampaigns->first()->status)->toBe('active');
    });
});

describe('Setting Model', function (): void {
    it('can create a setting', function (): void {
        $setting = Setting::factory()->create([
            'key' => 'test_setting',
            'value' => 'test_value',
            'display_name' => 'Test Setting',
        ]);

        expect($setting->key)->toBe('test_setting');
        expect($setting->value)->toBe('test_value');
        expect($setting->display_name)->toBe('Test Setting');
    });

    it('has correct fillable attributes', function (): void {
        $setting = new Setting;
        $fillable = $setting->getFillable();

        expect($fillable)->toContain('key');
        expect($fillable)->toContain('value');
        expect($fillable)->toContain('display_name');
        expect($fillable)->toContain('description');
    });
});

describe('Notification Model', function (): void {
    it('can create a notification', function (): void {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'type' => 'info',
            'title' => 'Test Notification',
            'message' => 'Test message',
            'user_id' => $user->id,
        ]);

        expect($notification->type)->toBe('info');
        expect($notification->title)->toBe('Test Notification');
        expect($notification->message)->toBe('Test message');
        expect($notification->user_id)->toBe($user->id);
    });

    it('belongs to a user', function (): void {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        expect($notification->user)->toBeInstanceOf(User::class);
        expect($notification->user->id)->toBe($user->id);
    });
});

describe('AnalyticsEvent Model', function (): void {
    it('can create an analytics event', function (): void {
        $user = User::factory()->create();

        $event = AnalyticsEvent::factory()->create([
            'event_name' => 'test_event',
            'event_type' => 'page_view',
            'user_id' => $user->id,
            'session_id' => 'test_session_123',
        ]);

        expect($event->event_name)->toBe('test_event');
        expect($event->event_type)->toBe('page_view');
        expect($event->user_id)->toBe($user->id);
        expect($event->session_id)->toBe('test_session_123');
    });

    it('belongs to a user', function (): void {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        expect($event->user)->toBeInstanceOf(User::class);
        expect($event->user->id)->toBe($user->id);
    });
});

describe('PartnerTier Model', function (): void {
    it('can create a partner tier', function (): void {
        $tier = PartnerTier::factory()->create([
            'name' => 'Gold Partner',
            'description' => 'Gold tier partner',
            'discount_percentage' => 15.0,
            'min_order_value' => 1000.0,
        ]);

        expect($tier->name)->toBe('Gold Partner');
        expect($tier->description)->toBe('Gold tier partner');
        expect($tier->discount_percentage)->toBe(15.0);
        expect($tier->min_order_value)->toBe(1000.0);
    });

    it('has correct fillable attributes', function (): void {
        $tier = new PartnerTier;
        $fillable = $tier->getFillable();

        expect($fillable)->toContain('name');
        expect($fillable)->toContain('description');
        expect($fillable)->toContain('discount_percentage');
        expect($fillable)->toContain('min_order_value');
    });
});

describe('Product Model', function (): void {
    it('can create a product', function (): void {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 99.99,
            'is_visible' => true,
        ]);

        expect($product->name)->toBe('Test Product');
        expect($product->slug)->toBe('test-product');
        expect($product->price)->toBe(99.99);
        expect($product->is_visible)->toBeTrue();
    });

    it('has correct fillable attributes', function (): void {
        $product = new Product;
        $fillable = $product->getFillable();

        expect($fillable)->toContain('name');
        expect($fillable)->toContain('slug');
        expect($fillable)->toContain('price');
        expect($fillable)->toContain('is_visible');
    });

    it('can scope visible products', function (): void {
        Product::factory()->create(['is_visible' => true]);
        Product::factory()->create(['is_visible' => false]);

        $visibleProducts = Product::visible()->get();

        expect($visibleProducts)->toHaveCount(1);
        expect($visibleProducts->first()->is_visible)->toBeTrue();
    });
});

describe('Order Model', function (): void {
    it('can create an order', function (): void {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 199.99,
            'status' => 'pending',
        ]);

        expect($order->user_id)->toBe($user->id);
        expect($order->total)->toBe(199.99);
        expect($order->status)->toBe('pending');
    });

    it('belongs to a user', function (): void {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        expect($order->user)->toBeInstanceOf(User::class);
        expect($order->user->id)->toBe($user->id);
    });

    it('has many order items', function (): void {
        $order = Order::factory()->create();
        $orderItem1 = \App\Models\OrderItem::factory()->create(['order_id' => $order->id]);
        $orderItem2 = \App\Models\OrderItem::factory()->create(['order_id' => $order->id]);

        expect($order->orderItems)->toHaveCount(2);
        expect($order->orderItems->first())->toBeInstanceOf(\App\Models\OrderItem::class);
    });
});

describe('Category Model', function (): void {
    it('can create a category', function (): void {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        expect($category->name)->toBe('Test Category');
        expect($category->slug)->toBe('test-category');
        expect($category->is_active)->toBeTrue();
    });

    it('can scope active categories', function (): void {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $activeCategories = Category::active()->get();

        expect($activeCategories)->toHaveCount(1);
        expect($activeCategories->first()->is_active)->toBeTrue();
    });
});

describe('Brand Model', function (): void {
    it('can create a brand', function (): void {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'is_active' => true,
        ]);

        expect($brand->name)->toBe('Test Brand');
        expect($brand->slug)->toBe('test-brand');
        expect($brand->is_active)->toBeTrue();
    });

    it('can scope active brands', function (): void {
        Brand::factory()->create(['is_active' => true]);
        Brand::factory()->create(['is_active' => false]);

        $activeBrands = Brand::active()->get();

        expect($activeBrands)->toHaveCount(1);
        expect($activeBrands->first()->is_active)->toBeTrue();
    });
});

describe('Collection Model', function (): void {
    it('can create a collection', function (): void {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'is_active' => true,
        ]);

        expect($collection->name)->toBe('Test Collection');
        expect($collection->slug)->toBe('test-collection');
        expect($collection->is_active)->toBeTrue();
    });

    it('can scope active collections', function (): void {
        Collection::factory()->create(['is_active' => true]);
        Collection::factory()->create(['is_active' => false]);

        $activeCollections = Collection::active()->get();

        expect($activeCollections)->toHaveCount(1);
        expect($activeCollections->first()->is_active)->toBeTrue();
    });
});

describe('Discount Model', function (): void {
    it('can create a discount', function (): void {
        $discount = Discount::factory()->create([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.0,
            'is_active' => true,
        ]);

        expect($discount->name)->toBe('Test Discount');
        expect($discount->type)->toBe('percentage');
        expect($discount->value)->toBe(10.0);
        expect($discount->is_active)->toBeTrue();
    });

    it('can scope active discounts', function (): void {
        Discount::factory()->create(['is_active' => true]);
        Discount::factory()->create(['is_active' => false]);

        $activeDiscounts = Discount::active()->get();

        expect($activeDiscounts)->toHaveCount(1);
        expect($activeDiscounts->first()->is_active)->toBeTrue();
    });
});

describe('Review Model', function (): void {
    it('can create a review', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        expect($review->user_id)->toBe($user->id);
        expect($review->product_id)->toBe($product->id);
        expect($review->rating)->toBe(5);
        expect($review->comment)->toBe('Great product!');
    });

    it('belongs to a user', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        expect($review->user)->toBeInstanceOf(User::class);
        expect($review->user->id)->toBe($user->id);
    });

    it('belongs to a product', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        expect($review->product)->toBeInstanceOf(Product::class);
        expect($review->product->id)->toBe($product->id);
    });
});
