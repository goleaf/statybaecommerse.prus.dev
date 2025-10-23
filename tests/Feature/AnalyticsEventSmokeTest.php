<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AnalyticsEventSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_analytics_event_links_to_user(): void
    {
        $user = User::factory()->create();

        $event = AnalyticsEvent::create([
            'event_name' => 'Test Event',
            'event_type' => 'page_view',
            'user_id' => $user->id,
            'session_id' => 'test-session-'.Str::uuid(),
        ]);

        $event->refresh();

        $this->assertSame('Test Event', $event->event_name);
        $this->assertSame('page_view', $event->event_type);
        $this->assertTrue($event->user->is($user));
        $this->assertNotEmpty($event->session_id);
    }
}
