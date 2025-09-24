<?php

declare(strict_types=1);

use App\Filament\Widgets\EnhancedEcommerceOverview;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->widget = new EnhancedEcommerceOverview;
});

it('can instantiate enhanced ecommerce overview widget', function () {
    expect($this->widget)->toBeInstanceOf(EnhancedEcommerceOverview::class);
});

it('returns stats array with correct structure', function () {
    // Create test data
    User::factory()->create();
    Product::factory()->create(['is_visible' => true]);
    Order::factory()->create(['status' => 'completed', 'total' => 100.00]);  // €100.00
    Review::factory()->create(['rating' => 5]);

    $stats = $this->widget->getStats();

    expect($stats)
        ->toBeArray()
        ->and(count($stats))
        ->toBe(6);

    // Check that each stat is a Stat object
    foreach ($stats as $stat) {
        expect($stat)
            ->toBeInstanceOf(\Filament\Widgets\StatsOverviewWidget\Stat::class);
    }
});

it('calculates total revenue correctly', function () {
    // Create completed orders
    Order::factory()->create(['status' => 'completed', 'total' => 100.00]);  // €100.00
    Order::factory()->create(['status' => 'completed', 'total' => 200.00]);  // €200.00
    Order::factory()->create(['status' => 'pending', 'total' => 50.00]);  // Should not count

    $stats = $this->widget->getStats();

    // Check that we have the expected number of stats
    expect($stats)->toHaveCount(6);

    // Test the actual calculation method directly
    $totalRevenue = $this->widget->getTotalRevenue();
    expect($totalRevenue)->toBe('€300.00');
});

it('counts total orders correctly', function () {
    Order::factory()->count(5)->create();

    $totalOrders = $this->widget->getTotalOrders();
    expect($totalOrders)->toBe('5');
});

it('counts customers correctly', function () {
    User::factory()->count(4)->create();

    $totalCustomers = $this->widget->getTotalCustomers();
    expect($totalCustomers)->toBe('4');
});

it('calculates average order value correctly', function () {
    Order::factory()->create(['status' => 'completed', 'total' => 100.00]);  // €100.00
    Order::factory()->create(['status' => 'completed', 'total' => 200.00]);  // €200.00

    $averageOrderValue = $this->widget->getAverageOrderValue();
    expect($averageOrderValue)->toBe('€150.00');
});

it('counts active products correctly', function () {
    Product::factory()->count(4)->create(['is_visible' => true]);
    Product::factory()->create(['is_visible' => false]);  // Should not count

    $totalProducts = $this->widget->getTotalProducts();
    expect($totalProducts)->toBe('4');
});

it('calculates average rating correctly', function () {
    Review::factory()->create(['rating' => 4]);
    Review::factory()->create(['rating' => 5]);
    Review::factory()->create(['rating' => 3]);

    $averageRating = $this->widget->getAverageRating();
    expect($averageRating)->toBe('4.0/5');
});

it('handles empty data gracefully', function () {
    $stats = $this->widget->getStats();

    expect($stats)
        ->toBeArray()
        ->and(count($stats))
        ->toBe(6);

    // Test individual methods with empty data
    expect($this->widget->getTotalRevenue())->toBe('€0.00');
    expect($this->widget->getTotalOrders())->toBe('0');
    expect($this->widget->getTotalCustomers())->toBe('0');
    expect($this->widget->getAverageOrderValue())->toBe('€0.00');
    expect($this->widget->getTotalProducts())->toBe('0');
    expect($this->widget->getAverageRating())->toBe('0.0/5');
});

it('has correct polling interval', function () {
    $reflection = new ReflectionClass($this->widget);
    $property = $reflection->getProperty('pollingInterval');
    $property->setAccessible(true);

    expect($property->getValue($this->widget))->toBe('15s');
});
