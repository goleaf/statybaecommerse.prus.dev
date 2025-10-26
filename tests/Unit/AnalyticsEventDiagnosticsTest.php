<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Unit-level assertions that cover the analytics event model behaviour.
 */
final class AnalyticsEventDiagnosticsTest extends TestCase
{
    private string $sqlitePath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = storage_path('framework/testing/analytics_event_diags.sqlite');
        $directory = dirname($this->sqlitePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        Config::set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('preferred_locale', 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_name')->nullable();
            $table->string('event_type')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_important')->default(false);
            $table->boolean('is_conversion')->default(false);
            $table->decimal('conversion_value', 10, 2)->nullable();
            $table->string('conversion_currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('trackable_type')->nullable();
            $table->unsignedBigInteger('trackable_id')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('properties')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->json('event_data')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('referrer_url')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['trackable_type', 'trackable_id']);
            $table->index(['device_type', 'created_at']);
            $table->index(['browser', 'created_at']);
            $table->index(['country_code', 'created_at']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('users');

        if ($this->sqlitePath !== '' && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    /**
     * The conversion currency accessor should gracefully fall back to EUR when null is provided.
     */
    public function test_conversion_currency_defaults_to_euro(): void
    {
        $event = AnalyticsEvent::factory()->create(['conversion_currency' => null]);

        $this->assertSame('EUR', $event->conversion_currency);
    }

    /**
     * The scoped query helpers must filter by event type correctly.
     */
    public function test_scope_filters_by_event_type(): void
    {
        $matching = AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'purchase']);

        $events = AnalyticsEvent::query()->byEventType('page_view')->get();

        $this->assertCount(1, $events);
        $this->assertTrue($events->first()?->is($matching));
    }

    /**
     * Analytics events should retain the owning user relationship when present.
     */
    public function test_user_relationship_resolves(): void
    {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->for($user)->create();

        $this->assertTrue($event->user()->is($user));
    }
}
