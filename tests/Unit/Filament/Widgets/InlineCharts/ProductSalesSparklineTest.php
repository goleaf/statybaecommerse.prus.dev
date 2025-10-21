<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Widgets\InlineCharts;

use App\Filament\Widgets\InlineCharts\ProductSalesSparkline;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Stats\Series\ProductSeries;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductSalesSparklineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirm the sparkline widget reuses the series helper payload.
     */
    public function test_widget_uses_series_helper_dataset(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $product = Product::factory()->create([
            'price' => 25,
        ]);

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
        ]);

        OrderItem::factory()
            ->forOrder($firstOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 1,
                'created_at' => $firstDay,
                'updated_at' => $firstDay,
            ]);

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
        ]);

        OrderItem::factory()
            ->forOrder($secondOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 4,
                'created_at' => $secondDay,
                'updated_at' => $secondDay,
            ]);

        $series = ProductSeries::dailySales($product);

        $expectedData = [
            'datasets' => [
                [
                    'label'           => __('products.sparkline.revenue_label', ['days' => count($series['labels'])]),
                    'data'            => $series['revenue'],
                    'borderWidth'     => 2,
                    'borderColor'     => 'rgba(37, 99, 235, 1)',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'fill'            => 'origin',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $series['labels'],
        ];

        $expectedChecksum = md5(json_encode($expectedData));

        Livewire::test(ProductSalesSparkline::class, [
            'record' => $product,
        ])->assertSet('dataChecksum', $expectedChecksum);

        CarbonImmutable::setTestNow();
    }
}
