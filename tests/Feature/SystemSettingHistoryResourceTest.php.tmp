<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SystemSettingHistoryResource;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingHistoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected SystemSettingCategory $category;

    protected SystemSetting $systemSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->systemSetting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'value' => 'original_value',
        ]);
    }

    public function test_can_list_system_setting_histories(): void
    {
        SystemSettingHistory::factory()->count(3)->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->assertCanSeeTableRecords(SystemSettingHistory::all());
    }

    public function test_can_create_system_setting_history(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by' => $this->adminUser->id,
                'change_reason' => 'Test change',
                'old_value' => 'old_value',
                'new_value' => 'new_value',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Test Browser)',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_histories', [
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'change_reason' => 'Test change',
            'old_value' => 'old_value',
            'new_value' => 'new_value',
        ]);
    }

    public function test_can_view_system_setting_history(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'change_reason' => 'Test change',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ViewSystemSettingHistory::class, [
            'record' => $history->getKey(),
        ])
            ->assertFormSet([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by' => $this->adminUser->id,
                'change_reason' => 'Test change',
            ]);
    }

    public function test_can_edit_system_setting_history(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'change_reason' => 'Original reason',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\EditSystemSettingHistory::class, [
            'record' => $history->getKey(),
        ])
            ->fillForm([
                'change_reason' => 'Updated reason',
                'ip_address' => '192.168.1.1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_histories', [
            'id' => $history->id,
            'change_reason' => 'Updated reason',
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_can_delete_system_setting_history(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\EditSystemSettingHistory::class, [
            'record' => $history->getKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('system_setting_histories', [
            'id' => $history->id,
        ]);
    }

    public function test_can_filter_by_system_setting(): void
    {
        $setting2 = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
            'key' => 'test_setting_2',
        ]);

        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $setting2->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->filterTable('system_setting_id', $this->systemSetting->id)
            ->assertCanSeeTableRecords(SystemSettingHistory::where('system_setting_id', $this->systemSetting->id)->get())
            ->assertCanNotSeeTableRecords(SystemSettingHistory::where('system_setting_id', $setting2->id)->get());
    }

    public function test_can_filter_by_changed_by(): void
    {
        $user2 = User::factory()->create(['name' => 'User 2']);

        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $user2->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->filterTable('changed_by', $this->adminUser->id)
            ->assertCanSeeTableRecords(SystemSettingHistory::where('changed_by', $this->adminUser->id)->get())
            ->assertCanNotSeeTableRecords(SystemSettingHistory::where('changed_by', $user2->id)->get());
    }

    public function test_can_search_system_setting_histories(): void
    {
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'change_reason' => 'Test change reason',
        ]);
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'change_reason' => 'Different reason',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords(SystemSettingHistory::where('change_reason', 'like', '%Test%')->get())
            ->assertCanNotSeeTableRecords(SystemSettingHistory::where('change_reason', 'like', '%Different%')->get());
    }

    public function test_can_restore_value_action(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'old_value' => 'restore_value',
            'new_value' => 'current_value',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->callTableAction('restore_value', $history)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_settings', [
            'id' => $this->systemSetting->id,
            'value' => 'restore_value',
        ]);
    }

    public function test_can_export_history(): void
    {
        SystemSettingHistory::factory()->count(3)->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->callTableBulkAction('export_history', SystemSettingHistory::all())
            ->assertHasNoBulkActionErrors();
    }

    public function test_validation_requires_system_setting_id(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->fillForm([
                'changed_by' => $this->adminUser->id,
                'change_reason' => 'Test change',
            ])
            ->call('create')
            ->assertHasFormErrors(['system_setting_id' => 'required']);
    }

    public function test_validation_requires_changed_by(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'change_reason' => 'Test change',
            ])
            ->call('create')
            ->assertHasFormErrors(['changed_by' => 'required']);
    }

    public function test_validation_accepts_valid_ip_address(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by' => $this->adminUser->id,
                'change_reason' => 'Test change',
                'ip_address' => '192.168.1.1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_validation_rejects_invalid_ip_address(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->fillForm([
                'system_setting_id' => $this->systemSetting->id,
                'changed_by' => $this->adminUser->id,
                'change_reason' => 'Test change',
                'ip_address' => 'invalid_ip',
            ])
            ->call('create')
            ->assertHasFormErrors(['ip_address']);
    }

    public function test_navigation_group_is_settings(): void
    {
        $this->assertEquals(
            'Settings',
            SystemSettingHistoryResource::getNavigationGroup()
        );
    }

    public function test_navigation_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.navigation_label'),
            SystemSettingHistoryResource::getNavigationLabel()
        );
    }

    public function test_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.model_label'),
            SystemSettingHistoryResource::getModelLabel()
        );
    }

    public function test_plural_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('admin.system_setting_histories.plural_model_label'),
            SystemSettingHistoryResource::getPluralModelLabel()
        );
    }

    public function test_record_title_attribute_is_change_reason(): void
    {
        $this->assertEquals('change_reason', SystemSettingHistoryResource::getRecordTitleAttribute());
    }

    public function test_navigation_sort_is_thirteen(): void
    {
        $this->assertEquals(13, SystemSettingHistoryResource::getNavigationSort());
    }

    public function test_form_sections_are_organized(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\CreateSystemSettingHistory::class)
            ->assertFormExists()
            ->assertFormFieldExists('system_setting_id')
            ->assertFormFieldExists('changed_by')
            ->assertFormFieldExists('change_reason')
            ->assertFormFieldExists('old_value')
            ->assertFormFieldExists('new_value')
            ->assertFormFieldExists('ip_address')
            ->assertFormFieldExists('user_agent');
    }

    public function test_table_columns_are_configured(): void
    {
        SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->assertCanSeeTableColumns([
                'systemSetting.key',
                'user.name',
                'change_reason',
                'old_value',
                'new_value',
                'ip_address',
                'created_at',
            ]);
    }

    public function test_table_filters_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->assertCanSeeTableFilters([
                'system_setting_id',
                'changed_by',
            ]);
    }

    public function test_table_actions_are_configured(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'old_value' => 'restore_value',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->assertCanSeeTableActions([
                'view',
                'edit',
                'restore_value',
            ]);
    }

    public function test_bulk_actions_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class)
            ->assertCanSeeBulkActions([
                'delete',
                'export_history',
            ]);
    }

    public function test_restore_value_action_only_visible_when_old_value_exists(): void
    {
        $historyWithOldValue = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'old_value' => 'restore_value',
        ]);

        $historyWithoutOldValue = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
            'old_value' => null,
        ]);

        $this->actingAs($this->adminUser);

        $component = Livewire::test(SystemSettingHistoryResource\Pages\ListSystemSettingHistories::class);

        // Should be able to see restore action for record with old value
        $component->assertCanSeeTableAction('restore_value', $historyWithOldValue);

        // Should not be able to see restore action for record without old value
        $component->assertCanNotSeeTableAction('restore_value', $historyWithoutOldValue);
    }

    public function test_relationships_work_correctly(): void
    {
        $history = SystemSettingHistory::factory()->create([
            'system_setting_id' => $this->systemSetting->id,
            'changed_by' => $this->adminUser->id,
        ]);

        $this->assertInstanceOf(SystemSetting::class, $history->systemSetting);
        $this->assertEquals($this->systemSetting->id, $history->systemSetting->id);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($this->adminUser->id, $history->user->id);
    }
}
