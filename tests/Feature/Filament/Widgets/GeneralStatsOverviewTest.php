<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\GeneralStatsOverview;
use App\Models\AnalyticsEvent;
use App\Models\Subscriber;
use Carbon\CarbonImmutable;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

final class GeneralStatsOverviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureWidgetTables();
    }

    public function test_it_generates_stats_without_errors(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2025-01-15 12:00:00'));

        Subscriber::query()->create([
            'email'         => 'news@example.com',
            'status'        => 'active',
            'subscribed_at' => now()->subDays(2),
            'created_at'    => now()->subDays(2),
            'updated_at'    => now()->subDays(2),
        ]);

        DB::table('orders')->insert([
            'id'             => 1,
            'number'         => 'ORD-001',
            'status'         => 'completed',
            'payment_status' => 'paid',
            'total'          => 120.00,
            'currency'       => 'EUR',
            'created_at'     => now()->subDay(),
            'updated_at'     => now()->subDay(),
        ]);

        DB::table('order_items')->insert([
            'order_id'   => 1,
            'quantity'   => 2,
            'total'      => 120.00,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        AnalyticsEvent::query()->create([
            'event_type'    => 'page_view',
            'session_id'    => 'session-1',
            'is_conversion' => true,
            'created_at'    => now()->subDay(),
            'updated_at'    => now()->subDay(),
        ]);

        AnalyticsEvent::query()->create([
            'event_type'    => 'page_view',
            'session_id'    => 'session-2',
            'is_conversion' => false,
            'created_at'    => now()->subDay(),
            'updated_at'    => now()->subDay(),
        ]);

        Cache::clear();

        $widget = app(GeneralStatsOverview::class);
        $widget->filter = 'month';

        $stats = $this->invokeWidgetMethod($widget, 'getStats');

        self::assertIsArray($stats);
        self::assertNotEmpty($stats);
        self::assertContainsOnlyInstancesOf(Stat::class, $stats);
    }

    /**
     * @return array<mixed>
     */
    private function invokeWidgetMethod(object $widget, string $method): array
    {
        $reflection = new ReflectionClass($widget);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        /** @var array<mixed> $result */
        $result = $target->invoke($widget);

        return $result;
    }

    private function ensureWidgetTables(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('users');

        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number');
            $table->foreignId('user_id')->nullable();
            $table->foreignId('customer_id')->nullable();
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

        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->string('session_id')->nullable();
            $table->boolean('is_conversion')->default(false);
            $table->decimal('conversion_value', 12, 2)->nullable();
            $table->string('conversion_currency', 3)->default('EUR');
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('status')->default('active');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }
}
