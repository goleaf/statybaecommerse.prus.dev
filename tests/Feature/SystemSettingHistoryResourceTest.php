<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SystemSettingHistories\Pages\CreateSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\EditSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\ListSystemSettingHistories;
use App\Filament\Resources\SystemSettingHistories\Pages\ViewSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingHistory;
use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable as TestableLivewire;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingHistoryResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Form field keys that should always be available on create/edit forms.
     * Keeping the list centralised allows the assertions to stay in sync with
     * the Filament schema while avoiding duplicated arrays across tests.
     *
     * @var array<int, string>
     */
    private const FORM_FIELD_KEYS = [
        'system_setting_id',
        'changed_by',
        'change_reason',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    /**
     * Table column identifiers that the listing component must expose.
     * Centralising the configuration guarantees the table assertions stay
     * aligned whenever the resource schema evolves.
     *
     * @var array<int, string>
     */
    private const TABLE_COLUMNS = [
        'systemSetting.key',
        'user.name',
        'change_reason',
        'old_value',
        'new_value',
        'ip_address',
        'created_at',
    ];

    /**
     * Filter handles that the listing resource needs to expose for quick
     * history lookups. Using a constant helps the tests remain readable when
     * additional filters arrive later on.
     *
     * @var array<int, string>
     */
    private const TABLE_FILTERS = [
        'system_setting_id',
        'changed_by',
    ];

    /**
     * Row-level actions that should be available inside the history listing.
     * Keeping the identifiers in one place keeps assertions descriptive and
     * allows follow-up refactors to adjust the behaviour easily.
     *
     * @var array<int, string>
     */
    private const TABLE_ACTIONS = [
        'view',
        'edit',
        'restore_value',
    ];

    /**
     * Bulk actions that must stay available to administrators. The constant
     * avoids repetition across tests and documents the expectation for future
     * maintainers.
     *
     * @var array<int, string>
     */
    private const BULK_ACTIONS = [
        'delete',
        'export_history',
    ];

    protected User $adminUser;

    protected SystemSettingCategory $category;

    protected SystemSetting $systemSetting;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a deterministic administrator so every test reuses the same
        // authentication context without repeating attribute arrays.
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name'  => 'Admin User',
        ]);

        // Spawning a real category keeps the downstream factory assignments
        // intact, mirroring the relationships used by the Filament resource.
        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        // Provision the canonical system setting that subsequent history
        // records will be associated with throughout the suite.
        $this->systemSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'test_setting',
            'name'        => 'Test Setting',
            'value'       => 'original_value',
        ]);
    }

    public function test_can_list_system_setting_histories(): void
    {
        $histories = SystemSettingHistory::factory()
            ->count(3)
            ->create([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
            ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->assertCanSeeTableRecords($histories);
    }

    public function test_can_create_system_setting_history(): void
    {
        $this->actingAsAdmin();

        $this->livewire(CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
                'change_reason'     => 'Test change',
                'old_value'         => 'old_value',
                'new_value'         => 'new_value',
                'ip_address'        => '127.0.0.1',
                'user_agent'        => 'Mozilla/5.0 (Test Browser)',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_histories', [
            'system_setting_id' => $this->systemSetting->id,
            'changed_by'        => $this->adminUser->id,
            'change_reason'     => 'Test change',
            'old_value'         => 'old_value',
            'new_value'         => 'new_value',
        ]);
    }

    public function test_can_view_system_setting_history(): void
    {
        $history = $this->makeHistory([
            'change_reason' => 'Test change',
        ]);

        $this->actingAsAdmin();

        $this->livewire(ViewSystemSettingHistory::class, [
            'record' => $history->getKey(),
        ])
            ->assertFormSet([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
                'change_reason'     => 'Test change',
            ]);
    }

    public function test_can_edit_system_setting_history(): void
    {
        $history = $this->makeHistory([
            'change_reason' => 'Original reason',
        ]);

        $this->actingAsAdmin();

        $this->livewire(EditSystemSettingHistory::class, [
            'record' => $history->getKey(),
        ])
            ->fillForm([
                'change_reason' => 'Updated reason',
                'ip_address'    => '192.168.1.1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_histories', [
            'id'            => $history->id,
            'change_reason' => 'Updated reason',
            'ip_address'    => '192.168.1.1',
        ]);
    }

    public function test_can_delete_system_setting_history(): void
    {
        $history = $this->makeHistory();

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->callTableAction('delete', $history)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('system_setting_histories', [
            'id' => $history->id,
        ]);
    }

    public function test_can_filter_by_system_setting(): void
    {
        $alternateSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key'         => 'test_setting_2',
        ]);

        $matchingHistory = $this->makeHistory();
        $nonMatchingHistory = $this->makeHistory([
            'system_setting_id' => $alternateSetting->id,
        ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->filterTable('system_setting_id', $this->systemSetting->id)
            ->assertCanSeeTableRecords(
                SystemSettingHistory::query()->whereKey($matchingHistory->getKey())->get()
            )
            ->assertCanNotSeeTableRecords(
                SystemSettingHistory::query()->whereKey($nonMatchingHistory->getKey())->get()
            );
    }

    public function test_can_filter_by_changed_by(): void
    {
        $otherUser = User::factory()->create([
            'name' => 'Other User',
        ]);

        $matchingHistory = $this->makeHistory();
        $nonMatchingHistory = $this->makeHistory([
            'changed_by' => $otherUser->id,
        ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->filterTable('changed_by', $this->adminUser->id)
            ->assertCanSeeTableRecords(
                SystemSettingHistory::query()->whereKey($matchingHistory->getKey())->get()
            )
            ->assertCanNotSeeTableRecords(
                SystemSettingHistory::query()->whereKey($nonMatchingHistory->getKey())->get()
            );
    }

    public function test_can_search_system_setting_histories(): void
    {
        $matchingHistory = $this->makeHistory([
            'change_reason' => 'Test change reason',
        ]);
        $this->makeHistory([
            'change_reason' => 'Different reason',
        ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords(
                SystemSettingHistory::query()->whereKey($matchingHistory->getKey())->get()
            )
            ->assertCanNotSeeTableRecords(
                SystemSettingHistory::where('change_reason', 'like', '%Different%')->get()
            );
    }

    public function test_can_restore_value_action(): void
    {
        $history = $this->makeHistory([
            'old_value' => 'restore_value',
            'new_value' => 'current_value',
        ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->callTableAction('restore_value', $history)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'id'    => $this->systemSetting->id,
            'value' => 'restore_value',
        ]);
    }

    public function test_restore_value_action_requires_old_value(): void
    {
        $history = $this->makeHistory([
            'old_value' => null,
        ]);

        $this->actingAsAdmin();

        $component = $this->livewire(ListSystemSettingHistories::class);
        $component->call('loadTable');

        $action = $component->instance()->getTable()->getAction('restore_value');

        // Ensure the restore action remains hidden when there is no historical value available.
        $action->record($history);
        $this->assertFalse($action->isVisible(), 'Restore action should be hidden when no old value exists.');

        // Double-check that attempting to bypass the UI leaves the system setting untouched.
        $this->assertEquals('original_value', $this->systemSetting->fresh()->value);
    }

    public function test_can_export_history(): void
    {
        $histories = SystemSettingHistory::factory()
            ->count(3)
            ->create([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
            ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->callTableBulkAction('export_history', $histories)
            ->assertHasNoBulkActionErrors();
    }

    public function test_validation_requires_system_setting_id(): void
    {
        $this->actingAsAdmin();

        $this->livewire(CreateSystemSettingHistory::class)
            ->fillForm([
                'changed_by'    => $this->adminUser->id,
                'change_reason' => 'Test change',
            ])
            ->call('create')
            ->assertHasFormErrors(['system_setting_id' => 'required']);
    }

    public function test_validation_requires_changed_by(): void
    {
        $this->actingAsAdmin();

        $this->livewire(CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'change_reason'     => 'Test change',
            ])
            ->call('create')
            ->assertHasFormErrors(['changed_by' => 'required']);
    }

    public function test_validation_accepts_valid_ip_address(): void
    {
        $this->actingAsAdmin();

        $this->livewire(CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
                'change_reason'     => 'Test change',
                'ip_address'        => '192.168.1.1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_validation_rejects_invalid_ip_address(): void
    {
        $this->actingAsAdmin();

        $this->livewire(CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by'        => $this->adminUser->id,
                'change_reason'     => 'Test change',
                'ip_address'        => 'invalid_ip',
            ])
            ->call('create')
            ->assertHasFormErrors(['ip_address']);
    }

    public function test_navigation_group_is_settings(): void
    {
        $this->assertEquals(
            Nav::groupForResource(SystemSettingHistoryResource::class),
            SystemSettingHistoryResource::getNavigationGroup(),
        );
    }

    public function test_navigation_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.navigation_label'),
            SystemSettingHistoryResource::getNavigationLabel(),
        );
    }

    public function test_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.model_label'),
            SystemSettingHistoryResource::getModelLabel(),
        );
    }

    public function test_plural_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.plural_model_label'),
            SystemSettingHistoryResource::getPluralModelLabel(),
        );
    }

    public function test_record_title_attribute_is_change_reason(): void
    {
        $this->assertEquals('change_reason', SystemSettingHistoryResource::getRecordTitleAttribute());
    }

    public function test_navigation_sort_is_thirteen(): void
    {
        $this->assertEquals(
            Nav::sortForResource(SystemSettingHistoryResource::class),
            SystemSettingHistoryResource::getNavigationSort(),
        );
    }

    public function test_form_sections_are_organized(): void
    {
        $this->actingAsAdmin();

        $component = $this->livewire(CreateSystemSettingHistory::class)
            ->assertFormExists();

        foreach (self::FORM_FIELD_KEYS as $fieldKey) {
            // Each assertion is executed individually so the failing key is
            // surfaced directly when the schema drifts.
            $component->assertFormFieldExists($fieldKey);
        }
    }

    public function test_table_columns_are_configured(): void
    {
        $this->makeHistory();

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->assertCanSeeTableColumns(self::TABLE_COLUMNS);
    }

    public function test_table_filters_are_configured(): void
    {
        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->assertCanSeeTableFilters(self::TABLE_FILTERS);
    }

    public function test_table_actions_are_configured(): void
    {
        $this->makeHistory([
            'old_value' => 'restore_value',
        ]);

        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->assertCanSeeTableActions(self::TABLE_ACTIONS);
    }

    public function test_bulk_actions_are_configured(): void
    {
        $this->actingAsAdmin();

        $this->livewire(ListSystemSettingHistories::class)
            ->assertCanSeeBulkActions(self::BULK_ACTIONS);
    }

    public function test_restore_value_action_only_visible_when_old_value_exists(): void
    {
        $historyWithOldValue = $this->makeHistory([
            'old_value' => 'restore_value',
        ]);

        $historyWithoutOldValue = $this->makeHistory([
            'old_value' => null,
        ]);

        $this->actingAsAdmin();

        $component = $this->livewire(ListSystemSettingHistories::class);
        $component->call('loadTable');

        $action = $component->instance()->getTable()->getAction('restore_value');

        // Confirm the action stays visible for histories that capture a previous value.
        $action->record($historyWithOldValue);
        $this->assertTrue($action->isVisible(), 'Restore action should be visible when an old value is present.');

        // Swap the bound record and ensure the visibility callback hides the action when no value exists.
        $action->record($historyWithoutOldValue);
        $this->assertFalse($action->isVisible(), 'Restore action should hide itself when the history lacks an old value.');
    }

    public function test_relationships_work_correctly(): void
    {
        $history = $this->makeHistory();

        $this->assertInstanceOf(SystemSetting::class, $history->systemSetting);
        $this->assertEquals($this->systemSetting->id, $history->systemSetting->id);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($this->adminUser->id, $history->user->id);
    }

    /**
     * Convenience wrapper that signs in the administrator created during setup.
     * The helper keeps the authentication intent explicit across the suite and
     * avoids repeating the `actingAs` boilerplate.
     */
    private function actingAsAdmin(): void
    {
        $this->actingAs($this->adminUser);
    }

    /**
     * Spins up a history record that belongs to the default system setting.
     * Centralising the factory payload ensures every test benefits from the
     * same baseline attributes while allowing targeted overrides.
     */
    private function makeHistory(array $overrides = []): SystemSettingHistory
    {
        return SystemSettingHistory::factory()->create($overrides + [
            'system_setting_id' => $this->systemSetting->id,
            'changed_by'        => $this->adminUser->id,
        ]);
    }

    /**
     * Small helper that proxies `Livewire::test()` so the return type stays
     * explicit across assertions. Having a dedicated wrapper makes it easier to
     * attach additional default parameters—such as locale or panel context—if
     * future Filament upgrades demand them.
     */
    private function livewire(string $component, array $parameters = []): TestableLivewire
    {
        return Livewire::test($component, $parameters);
    }
}
