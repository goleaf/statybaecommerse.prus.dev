<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AnalyticsEventScopeTest extends TestCase
{
    /**
     * Preserve the original activity log toggle so the scope suite does not leak
     * its configuration overrides into later tests that assert logging output.
     */
    private bool $originalActivityLogEnabled = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot the current activity log state before disabling it for the
        // analytics scope fixtures. Reapplying the saved value in tearDown keeps
        // downstream model tests from inheriting the disabled toggle.
        $this->originalActivityLogEnabled = (bool) config('activitylog.enabled', true);
        config(['activitylog.enabled' => false]);

        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('preferred_locale')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_name')->nullable();
            $table->string('event_type');
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->boolean('is_important')->default(false);
            $table->boolean('is_conversion')->default(false);
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('conversion_value', 12, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('conversion_currency')->default('EUR');
            $table->json('properties')->nullable();
            $table->json('event_data')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        // Reinstate the original configuration so other tests continue to
        // interact with the activity log exactly as the application expects.
        config(['activitylog.enabled' => $this->originalActivityLogEnabled]);

        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_scope_by_user_respects_authenticated_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        $otherUser = User::factory()->create([
            'is_admin' => false,
        ]);

        $userEvents = collect([
            AnalyticsEvent::create([
                'event_name' => 'User Event 1',
                'event_type' => 'page_view',
                'session_id' => 'session-user-1',
                'user_id'    => $user->id,
                'properties' => [],
                'event_data' => [],
            ]),
            AnalyticsEvent::create([
                'event_name' => 'User Event 2',
                'event_type' => 'page_view',
                'session_id' => 'session-user-2',
                'user_id'    => $user->id,
                'properties' => [],
                'event_data' => [],
            ]),
        ]);

        AnalyticsEvent::create([
            'event_name' => 'Other Event 1',
            'event_type' => 'page_view',
            'session_id' => 'session-other-1',
            'user_id'    => $otherUser->id,
            'properties' => [],
            'event_data' => [],
        ]);

        AnalyticsEvent::create([
            'event_name' => 'Other Event 2',
            'event_type' => 'page_view',
            'session_id' => 'session-other-2',
            'user_id'    => $otherUser->id,
            'properties' => [],
            'event_data' => [],
        ]);

        $this->actingAs($user);

        $events = AnalyticsEvent::byUser($user->id)->get();

        $this->assertCount($userEvents->count(), $events);
        $this->assertTrue($events->every(fn ($event) => $event->user_id === $user->id));
        $this->assertTrue($events->doesntContain(fn ($event) => $event->user_id === $otherUser->id));

        $otherEvents = AnalyticsEvent::byUser($otherUser->id)->get();
        $this->assertTrue($otherEvents->isEmpty());
    }
}
