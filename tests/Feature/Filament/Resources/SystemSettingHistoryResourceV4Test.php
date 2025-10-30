<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingHistoryResource\Pages\ListSystemSettingHistories;
use App\Models\SystemSetting;
use App\Models\SystemSettingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Targeted coverage for the Filament v4 SystemSettingHistory resource.
 */
final class SystemSettingHistoryResourceV4Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private SystemSetting $systemSetting;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the admin panel so Filament-powered Livewire components boot in the expected context.
        $this->resolveAdminPanel();

        // Keep locale-dependent factories deterministic across assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed an administrator that satisfies Filament authorization policies for every request in this suite.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);

        // Create the canonical setting record that test histories will be attached to for consistent foreign keys.
        $this->systemSetting = SystemSetting::factory()->create([
            'key'   => 'site_name',
            'name'  => 'Site name',
            'value' => 'Original Name',
        ]);
    }

    public function test_list_page_renders_seeded_histories(): void
    {
        // Persist a history entry so the list view has concrete data to display.
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->getKey(),
            'changed_by'        => $this->admin->getKey(),
            'change_reason'     => 'Initial configuration',
        ]);

        // Ensure the Livewire table hydrates before verifying the record visibility.
        Livewire::test(ListSystemSettingHistories::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$history]);
    }

    public function test_filters_scope_histories_by_setting_and_user(): void
    {
        // Provision an alternative setting to validate that the setting filter narrows the dataset correctly.
        $otherSetting = SystemSetting::factory()->create([
            'key'   => 'timezone',
            'name'  => 'Timezone',
            'value' => 'UTC',
        ]);

        // Create an auxiliary user who authored a competing history record.
        $secondaryUser = User::factory()->create();

        // Insert the target history expected to remain visible after both filters run.
        $matchingHistory = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->getKey(),
            'changed_by'        => $this->admin->getKey(),
        ]);

        // Insert a control history that should disappear once the filters are applied.
        $otherHistory = SystemSettingHistory::factory()->create([
            'system_setting_id' => $otherSetting->getKey(),
            'changed_by'        => $secondaryUser->getKey(),
        ]);

        // Apply both filters and confirm only the relevant record remains in the table output.
        Livewire::test(ListSystemSettingHistories::class)
            ->call('loadTable')
            ->filterTable('system_setting_id', (string) $this->systemSetting->getKey())
            ->filterTable('changed_by', (string) $this->admin->getKey())
            ->assertCanSeeTableRecords([$matchingHistory])
            ->assertCanNotSeeTableRecords([$otherHistory]);
    }

    public function test_restore_value_action_updates_system_setting(): void
    {
        // Seed a history entry with a meaningful old value so the restore action has work to perform.
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->getKey(),
            'changed_by'        => $this->admin->getKey(),
            'old_value'         => 'Restored Value',
            'new_value'         => 'Superseded Value',
        ]);

        // Bump the current setting value to confirm the restore action overwrites it successfully.
        $this->systemSetting->update(['value' => 'Changed Value']);

        // Trigger the restore action via the Filament table to simulate real operator behaviour.
        Livewire::test(ListSystemSettingHistories::class)
            ->call('loadTable')
            ->callTableAction('restore_value', $history);

        // Reload the model and confirm the old value has been reinstated by the table action.
        $this->assertSame('Restored Value', $this->systemSetting->fresh()->value);
    }
}
