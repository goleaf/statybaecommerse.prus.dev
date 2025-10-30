<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prime the Filament admin panel context so Livewire resources resolve correctly in tests.
        $this->resolveAdminPanel();

        // Normalise the locale to English so translated column labels remain deterministic.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and authenticate an administrator to bypass Filament policies during assertions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_activity_logs_and_supports_filters(): void
    {
        // Seed representative activity records covering different log names and subjects for the filters to target.
        $actor = User::factory()->create(['name' => 'Event Actor']);

        $visibleLog = ActivityLog::factory()->create([
            'log_name'     => 'auth',
            'event'        => 'login',
            'subject_type' => User::class,
            'subject_id'   => $actor->getKey(),
            'causer_type'  => User::class,
            'causer_id'    => $actor->getKey(),
            'description'  => 'Admin logged in',
            'created_at'   => now()->subDay(),
        ]);

        $hiddenLog = ActivityLog::factory()->create([
            'log_name'     => 'system',
            'event'        => 'updated',
            'subject_type' => 'App\\Models\\Order',
            'description'  => 'Order updated',
            'created_at'   => now()->subWeeks(2),
        ]);

        // Load the table before asserting so deferred datasets hydrate as instructed in the agent notes.
        Livewire::test(ListActivityLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$visibleLog, $hiddenLog])
            // Filter by log name and ensure only the matching record remains visible.
            ->filterTable('log_name', 'auth')
            ->assertCanSeeTableRecords([$visibleLog])
            ->assertCanNotSeeTableRecords([$hiddenLog])
            // Chain a subject type filter to confirm enum-style selections still surface the seeded record.
            ->filterTable('subject_type', User::class)
            ->assertCanSeeTableRecords([$visibleLog])
            // Finally clamp the date range around the recent record to exclude the stale event entirely.
            ->filterTable('created_at', [
                'range' => [
                    'start' => now()->subDays(2)->format('Y-m-d'),
                    'end'   => now()->format('Y-m-d'),
                ],
            ])
            ->assertCanSeeTableRecords([$visibleLog]);
    }

    public function test_view_details_table_action_mounts_selected_record(): void
    {
        // Persist a concise activity entry so the modal heading and subheading have deterministic content.
        $actor = User::factory()->create(['name' => 'Modal Actor']);

        $activity = ActivityLog::factory()->create([
            'log_name'     => 'system',
            'event'        => 'updated',
            'description'  => 'System configuration updated',
            'request_id'   => (string) Str::uuid(),
            'causer_type'  => User::class,
            'causer_id'    => $actor->getKey(),
            'subject_type' => User::class,
            'subject_id'   => $actor->getKey(),
        ]);

        // Trigger the table action and ensure Filament mounts the expected record without surfacing validation errors.
        Livewire::test(ListActivityLogs::class)
            ->call('loadTable')
            ->callTableAction('view_details', $activity)
            ->assertSet('mountedTableAction', 'view_details')
            ->assertSet('mountedTableActionRecordKey', (string) $activity->getKey())
            ->assertHasNoTableActionErrors();
    }

    public function test_record_title_falls_back_to_identifier_when_description_missing(): void
    {
        // Create an activity entry lacking a description to exercise the fallback title logic.
        $activity = ActivityLog::factory()->create([
            'description' => null,
        ]);

        // The resource should prefer the generated "Activity Log #id" label when no description is available.
        $expectedTitle = __('activity_logs.single') . ' #' . $activity->getKey();
        self::assertSame($expectedTitle, ActivityLogResource::getRecordTitle($activity));

        // When invoked without a record the resource should fall back to the base translation key.
        self::assertSame(__('activity_logs.single'), ActivityLogResource::getRecordTitle(null));
    }
}
