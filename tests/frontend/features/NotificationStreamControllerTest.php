<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class NotificationStreamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    public function test_stream_returns_server_sent_events_response(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream');
        $response->assertHeader('Cache-Control', 'no-cache');
        $response->assertHeader('Connection', 'keep-alive');
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $response->assertHeader('Access-Control-Allow-Headers', 'Cache-Control');
    }

    public function test_stream_returns_connection_confirmation(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        // Check that the response contains the initial connection confirmation
        $content = $response->getContent();
        $this->assertStringContainsString('data: {"type":"connected"', $content);
        $this->assertStringContainsString('"message":"Connected to live notifications"', $content);
    }

    public function test_stream_returns_heartbeat_messages(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        // Check that the response contains heartbeat structure
        $content = $response->getContent();
        $this->assertStringContainsString('"type":"heartbeat"', $content);
    }

    public function test_stream_detects_new_notifications(): void
    {
        // Create a notification before starting the stream
        DatabaseNotification::create([
            'id' => 'test-stream-notification',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Stream Test',
                'message' => 'Test notification for stream',
                'type' => 'info'
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"type":"notification"', $content);
        $this->assertStringContainsString('"title":"Stream Test"', $content);
        $this->assertStringContainsString('"message":"Test notification for stream"', $content);
        $this->assertStringContainsString('"type":"info"', $content);
    }

    public function test_stream_handles_multiple_notifications(): void
    {
        // Create multiple notifications
        DatabaseNotification::create([
            'id' => 'test-stream-1',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Notification 1',
                'message' => 'First notification',
                'type' => 'info'
            ],
        ]);

        DatabaseNotification::create([
            'id' => 'test-stream-2',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Notification 2',
                'message' => 'Second notification',
                'type' => 'warning'
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"title":"Notification 1"', $content);
        $this->assertStringContainsString('"title":"Notification 2"', $content);
        $this->assertStringContainsString('"message":"First notification"', $content);
        $this->assertStringContainsString('"message":"Second notification"', $content);
    }

    public function test_stream_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->get('/api/notifications/stream');

        $response->assertStatus(401);
    }

    public function test_stream_includes_notification_timestamp(): void
    {
        DatabaseNotification::create([
            'id' => 'test-timestamp',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Timestamp Test',
                'message' => 'Test with timestamp',
                'type' => 'info'
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"timestamp"', $content);
    }

    public function test_stream_handles_notifications_with_missing_data(): void
    {
        DatabaseNotification::create([
            'id' => 'test-missing-data',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [], // Empty data
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"title":"Notification"', $content);
        $this->assertStringContainsString('"message":""', $content);
        $this->assertStringContainsString('"type":"info"', $content);
    }

    public function test_stream_handles_different_notification_types(): void
    {
        // Test with different notification data structures
        DatabaseNotification::create([
            'id' => 'test-different-type',
            'type' => TestNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Different Type',
                'message' => 'Test with different type',
                'type' => 'error'
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"type":"error"', $content);
    }

    public function test_stream_handles_no_notifications(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/notifications/stream');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('"type":"connected"', $content);
        $this->assertStringContainsString('"type":"heartbeat"', $content);
    }
}
