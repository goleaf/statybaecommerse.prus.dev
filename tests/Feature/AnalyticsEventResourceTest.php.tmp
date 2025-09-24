<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AnalyticsEventResource\Pages\CreateAnalyticsEvent;
use App\Filament\Resources\AnalyticsEventResource\Pages\EditAnalyticsEvent;
use App\Filament\Resources\AnalyticsEventResource\Pages\ListAnalyticsEvents;
use App\Filament\Resources\AnalyticsEventResource\Pages\ViewAnalyticsEvent;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AnalyticsEventResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        // Create a test user for authentication
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_analytics_events(): void
    {
        // Arrange
        $analyticsEvents = AnalyticsEvent::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->assertCanSeeTableRecords($analyticsEvents);
    }

    public function test_can_create_analytics_event(): void
    {
        // Arrange
        $user = User::factory()->create();
        $analyticsEventData = [
            'event_name' => 'Test Event',
            'event_type' => 'page_view',
            'description' => 'Test analytics event',
            'user_id' => $user->id,
            'session_id' => 'test_session_123',
            'ip_address' => '192.168.1.1',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'country' => 'Lithuania',
            'city' => 'Vilnius',
            'is_important' => true,
            'is_conversion' => false,
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAnalyticsEvent::class)
            ->fillForm($analyticsEventData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('analytics_events', [
            'event_name' => 'Test Event',
            'event_type' => 'page_view',
            'description' => 'Test analytics event',
            'user_id' => $user->id,
            'session_id' => 'test_session_123',
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_can_edit_analytics_event(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create();
        $newDescription = 'Updated description';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAnalyticsEvent::class, ['record' => $analyticsEvent->id])
            ->fillForm(['description' => $newDescription])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('analytics_events', [
            'id' => $analyticsEvent->id,
            'description' => $newDescription,
        ]);
    }

    public function test_can_view_analytics_event(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewAnalyticsEvent::class, ['record' => $analyticsEvent->id])
            ->assertSee($analyticsEvent->event_name);
    }

    public function test_can_filter_analytics_events_by_type(): void
    {
        // Arrange
        $pageViewEvents = AnalyticsEvent::factory()->count(3)->create(['event_type' => 'page_view']);
        $clickEvents = AnalyticsEvent::factory()->count(2)->create(['event_type' => 'click']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->filterTable('event_type', 'page_view')
            ->assertCanSeeTableRecords($pageViewEvents)
            ->assertCanNotSeeTableRecords($clickEvents);
    }

    public function test_can_filter_analytics_events_by_importance(): void
    {
        // Arrange
        $importantEvents = AnalyticsEvent::factory()->count(3)->create(['is_important' => true]);
        $normalEvents = AnalyticsEvent::factory()->count(2)->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->filterTable('is_important', '1')
            ->assertCanSeeTableRecords($importantEvents)
            ->assertCanNotSeeTableRecords($normalEvents);
    }

    public function test_can_filter_analytics_events_by_conversion(): void
    {
        // Arrange
        $conversionEvents = AnalyticsEvent::factory()->count(3)->create(['is_conversion' => true]);
        $nonConversionEvents = AnalyticsEvent::factory()->count(2)->create(['is_conversion' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->filterTable('is_conversion', '1')
            ->assertCanSeeTableRecords($conversionEvents)
            ->assertCanNotSeeTableRecords($nonConversionEvents);
    }

    public function test_can_mark_analytics_event_as_important(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableAction('mark_important', $analyticsEvent)
            ->assertNotified();

        $this->assertDatabaseHas('analytics_events', [
            'id' => $analyticsEvent->id,
            'is_important' => true,
        ]);
    }

    public function test_can_mark_analytics_event_as_conversion(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create(['is_conversion' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableAction('mark_conversion', $analyticsEvent)
            ->assertNotified();

        $this->assertDatabaseHas('analytics_events', [
            'id' => $analyticsEvent->id,
            'is_conversion' => true,
        ]);
    }

    public function test_can_bulk_mark_analytics_events_as_important(): void
    {
        // Arrange
        $analyticsEvents = AnalyticsEvent::factory()->count(3)->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableBulkAction('mark_important', $analyticsEvents)
            ->assertNotified();

        foreach ($analyticsEvents as $analyticsEvent) {
            $this->assertDatabaseHas('analytics_events', [
                'id' => $analyticsEvent->id,
                'is_important' => true,
            ]);
        }
    }

    public function test_can_bulk_mark_analytics_events_as_conversions(): void
    {
        // Arrange
        $analyticsEvents = AnalyticsEvent::factory()->count(3)->create(['is_conversion' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableBulkAction('mark_conversions', $analyticsEvents)
            ->assertNotified();

        foreach ($analyticsEvents as $analyticsEvent) {
            $this->assertDatabaseHas('analytics_events', [
                'id' => $analyticsEvent->id,
                'is_conversion' => true,
            ]);
        }
    }

    public function test_can_search_analytics_events(): void
    {
        // Arrange
        $searchableEvent = AnalyticsEvent::factory()->create(['event_name' => 'Unique Event Name']);
        $otherEvents = AnalyticsEvent::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->searchTable('Unique Event Name')
            ->assertCanSeeTableRecords([$searchableEvent])
            ->assertCanNotSeeTableRecords($otherEvents);
    }

    public function test_can_sort_analytics_events_by_created_at(): void
    {
        // Arrange
        $oldEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDay()]);
        $newEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newEvent, $oldEvent], inOrder: true);
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAnalyticsEvent::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['event_name']);
    }

    public function test_validates_ip_address_format(): void
    {
        // Arrange
        $analyticsEventData = [
            'event_name' => 'Test Event',
            'ip_address' => 'invalid_ip',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAnalyticsEvent::class)
            ->fillForm($analyticsEventData)
            ->call('create')
            ->assertHasFormErrors(['ip_address']);
    }

    public function test_can_delete_analytics_event(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableAction('delete', $analyticsEvent)
            ->assertNotified();

        $this->assertDatabaseMissing('analytics_events', ['id' => $analyticsEvent->id]);
    }

    public function test_can_bulk_delete_analytics_events(): void
    {
        // Arrange
        $analyticsEvents = AnalyticsEvent::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->callTableBulkAction('delete', $analyticsEvents)
            ->assertNotified();

        foreach ($analyticsEvents as $analyticsEvent) {
            $this->assertDatabaseMissing('analytics_events', ['id' => $analyticsEvent->id]);
        }
    }

    public function test_can_set_conversion_value(): void
    {
        // Arrange
        $analyticsEvent = AnalyticsEvent::factory()->create([
            'is_conversion' => true,
            'conversion_value' => null,
            'conversion_currency' => null,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAnalyticsEvent::class, ['record' => $analyticsEvent->id])
            ->fillForm([
                'conversion_value' => 99.99,
                'conversion_currency' => 'EUR',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('analytics_events', [
            'id' => $analyticsEvent->id,
            'conversion_value' => 99.99,
            'conversion_currency' => 'EUR',
        ]);
    }

    public function test_can_filter_by_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $userEvents = AnalyticsEvent::factory()->count(3)->create(['user_id' => $user->id]);
        $otherEvents = AnalyticsEvent::factory()->count(2)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAnalyticsEvents::class)
            ->filterTable('user_id', (string) $user->id)
            ->assertCanSeeTableRecords($userEvents)
            ->assertCanNotSeeTableRecords($otherEvents);
    }
}
