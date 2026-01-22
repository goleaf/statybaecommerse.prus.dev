<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\SalesByMonthChart;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

final class SalesByMonthChartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureWidgetTables();
    }

    public function test_it_returns_chart_data(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2025-06-15 12:00:00'));

        Order::query()->create([
            'number'         => 'ORD-002',
            'status'         => 'completed',
            'payment_status' => 'paid',
            'total'          => 320.50,
            'currency'       => 'EUR',
            'created_at'     => now()->subMonths(2),
            'updated_at'     => now()->subMonths(2),
        ]);

        OrderItem::query()->create([
            'order_id'   => 1,
            'quantity'   => 1,
            'total'      => 320.50,
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonths(2),
        ]);

        Cache::clear();

        $widget = app(SalesByMonthChart::class);
        $widget->filter = 'year';

        $data = $this->invokeWidgetMethod($widget, 'getData');

        self::assertArrayHasKey('datasets', $data);
        self::assertArrayHasKey('labels', $data);
        self::assertNotEmpty($data['datasets']);
        self::assertNotEmpty($data['labels']);

        $badge = $this->invokeWidgetMethod($widget, 'getBadge');
        self::assertIsString($badge);
        self::assertNotSame('', $badge);
    }

    /**
     * @return mixed
     */
    private function invokeWidgetMethod(object $widget, string $method)
    {
        $reflection = new ReflectionClass($widget);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invoke($widget);
    }

    private function ensureWidgetTables(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::enableForeignKeyConstraints();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number');
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency')->default('EUR');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->string('name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }
}
