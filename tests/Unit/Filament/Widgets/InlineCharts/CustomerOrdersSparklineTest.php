<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Widgets\InlineCharts;

use App\Filament\Widgets\InlineCharts\CustomerOrdersSparkline;
use App\Models\Customer;
use App\Models\Order;
use App\Support\Stats\Series\CustomerSeries;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CustomerOrdersSparklineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirm the customer sparkline widget reflects the cached helper output.
     */
    public function test_widget_uses_series_helper_dataset(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $customer = Customer::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
            'total'      => 95.25,
        ]);

        Order::withoutTimestamps(function () use ($firstOrder, $customer): void {
            $firstOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
            'total'      => 42.75,
        ]);

        Order::withoutTimestamps(function () use ($secondOrder, $customer): void {
            $secondOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $series = CustomerSeries::dailyOrders($customer);

        $expectedData = [
            'datasets' => [
                [
                    'label'           => __('customers.sparkline.orders_label', ['days' => count($series['labels'])]),
                    'data'            => $series['orders'],
                    'borderWidth'     => 2,
                    'borderColor'     => 'rgba(34, 197, 94, 1)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'fill'            => 'origin',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $series['labels'],
        ];

        $expectedChecksum = md5(json_encode($expectedData));

        Livewire::test(CustomerOrdersSparkline::class, [
            'record' => $customer,
        ])->assertSet('dataChecksum', $expectedChecksum);

        CarbonImmutable::setTestNow();
    }
}
