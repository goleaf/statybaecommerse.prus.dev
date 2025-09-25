<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingDependencyResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_system_setting_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->assertCanSeeTableRecords([
                SystemSettingDependency::first(),
            ]);
    }

    public function test_can_create_system_setting_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\CreateSystemSettingDependency::class)
            ->fillForm([
                'setting_id' => $setting1->id,
                'depends_on_setting_id' => $setting2->id,
                'condition' => 'setting2.value == "enabled"',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);
    }

    public function test_can_edit_system_setting_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);
        $setting3 = SystemSetting::factory()->create(['key' => 'setting3']);

        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\EditSystemSettingDependency::class, [
            'record' => $dependency->getRouteKey(),
        ])
            ->fillForm([
                'setting_id' => $setting1->id,
                'depends_on_setting_id' => $setting3->id,
                'condition' => 'setting3.value == "enabled"',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency->id,
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting3->id,
            'condition' => 'setting3.value == "enabled"',
            'is_active' => false,
        ]);
    }

    public function test_can_view_system_setting_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        $this
            ->get(\App\Filament\Resources\SystemSettingDependencyResource::getUrl('view', ['record' => $dependency]))
            ->assertOk();
    }

    public function test_can_filter_by_setting(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);
        $setting3 = SystemSetting::factory()->create(['key' => 'setting3']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting3->id,
            'depends_on_setting_id' => $setting1->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->filterTable('setting_id', $setting1->id)
            ->assertCanSeeTableRecords([$dependency1])
            ->assertCanNotSeeTableRecords([$dependency2]);
    }

    public function test_can_filter_by_depends_on_setting(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);
        $setting3 = SystemSetting::factory()->create(['key' => 'setting3']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting3->id,
            'depends_on_setting_id' => $setting1->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->filterTable('depends_on_setting_id', $setting2->id)
            ->assertCanSeeTableRecords([$dependency1])
            ->assertCanNotSeeTableRecords([$dependency2]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $activeDependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'is_active' => true,
        ]);

        $inactiveDependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeDependency])
            ->assertCanNotSeeTableRecords([$inactiveDependency]);
    }

    public function test_can_toggle_active_status(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->callTableAction('toggle_active', $dependency)
            ->assertNotified();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency->id,
            'is_active' => false,
        ]);
    }

    public function test_can_duplicate_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->callTableAction('duplicate', $dependency)
            ->assertNotified();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled" (Copy)',
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_activate_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'is_active' => false,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->callTableBulkAction('activate', [$dependency1, $dependency2])
            ->assertNotified();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency1->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency2->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_deactivate_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'is_active' => true,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->callTableBulkAction('deactivate', [$dependency1, $dependency2])
            ->assertNotified();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency2->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_duplicate_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'condition' => 'setting1.value == "enabled"',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->callTableBulkAction('duplicate', [$dependency1, $dependency2])
            ->assertNotified();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled" (Copy)',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('system_setting_dependencies', [
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'condition' => 'setting1.value == "enabled" (Copy)',
            'is_active' => false,
        ]);
    }

    public function test_can_search_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);
        $setting3 = SystemSetting::factory()->create(['key' => 'setting3']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting3->id,
            'depends_on_setting_id' => $setting1->id,
            'condition' => 'setting1.value == "enabled"',
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->searchTable('setting1')
            ->assertCanSeeTableRecords([$dependency1, $dependency2]);
    }

    public function test_can_sort_dependencies(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'depends_on_setting_id' => $setting1->id,
            'condition' => 'setting1.value == "enabled"',
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\ListSystemSettingDependencies::class)
            ->sortTable('condition')
            ->assertCanSeeTableRecords([$dependency1, $dependency2]);
    }

    public function test_validation_requires_setting_id(): void
    {
        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\CreateSystemSettingDependency::class)
            ->fillForm([
                'setting_id' => null,
                'depends_on_setting_id' => 1,
                'condition' => 'test condition',
            ])
            ->call('create')
            ->assertHasFormErrors(['setting_id' => 'required']);
    }

    public function test_validation_requires_depends_on_setting_id(): void
    {
        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\CreateSystemSettingDependency::class)
            ->fillForm([
                'setting_id' => 1,
                'depends_on_setting_id' => null,
                'condition' => 'test condition',
            ])
            ->call('create')
            ->assertHasFormErrors(['depends_on_setting_id' => 'required']);
    }

    public function test_validation_requires_condition(): void
    {
        Livewire::test(\App\Filament\Resources\SystemSettingDependencyResource\Pages\CreateSystemSettingDependency::class)
            ->fillForm([
                'setting_id' => 1,
                'depends_on_setting_id' => 2,
                'condition' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['condition' => 'required']);
    }
}
