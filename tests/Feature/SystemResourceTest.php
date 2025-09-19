<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test category
        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test category for system settings',
        ]);
    }

    public function test_can_list_system_settings(): void
    {
        SystemSetting::factory()->count(3)->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->assertCanSeeTableRecords(SystemSetting::all());
    }

    public function test_can_create_system_setting(): void
    {
        $data = [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'description' => 'A test system setting',
            'type' => 'string',
            'value' => 'test_value',
            'category_id' => $this->category->id,
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_setting',
            'name' => 'Test Setting',
            'type' => 'string',
            'value' => 'test_value',
        ]);
    }

    public function test_can_edit_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $data = [
            'name' => 'Updated Setting Name',
            'value' => 'updated_value',
        ];

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\EditSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->fillForm($data)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'name' => 'Updated Setting Name',
            'value' => 'updated_value',
        ]);
    }

    public function test_can_view_system_setting(): void
    {
        $setting = SystemSetting::factory()->create([
            'category_id' => $this->category->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ViewSystem::class, [
            'record' => $setting->getKey(),
        ])
            ->assertCanSeeTableRecords([$setting]);
    }

    public function test_can_filter_system_settings_by_category(): void
    {
        $category1 = SystemSettingCategory::factory()->create(['name' => 'Category 1']);
        $category2 = SystemSettingCategory::factory()->create(['name' => 'Category 2']);

        $setting1 = SystemSetting::factory()->create(['category_id' => $category1->id]);
        $setting2 = SystemSetting::factory()->create(['category_id' => $category2->id]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('category', $category1->id)
            ->assertCanSeeTableRecords([$setting1])
            ->assertCanNotSeeTableRecords([$setting2]);
    }

    public function test_can_filter_system_settings_by_type(): void
    {
        $stringSetting = SystemSetting::factory()->create(['type' => 'string']);
        $booleanSetting = SystemSetting::factory()->create(['type' => 'boolean']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords([$stringSetting])
            ->assertCanNotSeeTableRecords([$booleanSetting]);
    }

    public function test_can_filter_required_settings(): void
    {
        $requiredSetting = SystemSetting::factory()->create(['is_required' => true]);
        $optionalSetting = SystemSetting::factory()->create(['is_required' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('required')
            ->assertCanSeeTableRecords([$requiredSetting])
            ->assertCanNotSeeTableRecords([$optionalSetting]);
    }

    public function test_can_filter_public_settings(): void
    {
        $publicSetting = SystemSetting::factory()->create(['is_public' => true]);
        $privateSetting = SystemSetting::factory()->create(['is_public' => false]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->filterTable('public')
            ->assertCanSeeTableRecords([$publicSetting])
            ->assertCanNotSeeTableRecords([$privateSetting]);
    }

    public function test_can_search_system_settings(): void
    {
        $setting1 = SystemSetting::factory()->create(['name' => 'Test Setting 1']);
        $setting2 = SystemSetting::factory()->create(['name' => 'Another Setting']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->searchTable('Test Setting')
            ->assertCanSeeTableRecords([$setting1])
            ->assertCanNotSeeTableRecords([$setting2]);
    }

    public function test_system_setting_validation(): void
    {
        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm([
                'key' => '',  // Required field
                'name' => '',  // Required field
                'type' => '',  // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['key', 'name', 'type']);
    }

    public function test_system_setting_unique_key(): void
    {
        $existingSetting = SystemSetting::factory()->create(['key' => 'existing_key']);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\CreateSystem::class)
            ->fillForm([
                'key' => 'existing_key',  // Duplicate key
                'name' => 'Test Setting',
                'type' => 'string',
                'value' => 'test_value',
                'category_id' => $this->category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_system_setting_tabs_functionality(): void
    {
        $generalCategory = SystemSettingCategory::factory()->create(['name' => 'General']);
        $securityCategory = SystemSettingCategory::factory()->create(['name' => 'Security']);

        $generalSetting = SystemSetting::factory()->create(['category_id' => $generalCategory->id]);
        $securitySetting = SystemSetting::factory()->create(['category_id' => $securityCategory->id]);

        Livewire::test(\App\Filament\Resources\SystemResource\Pages\ListSystems::class)
            ->assertCanSeeTableRecords([$generalSetting, $securitySetting])
            ->assertCanSeeTableRecords([$generalSetting])
            ->assertCanSeeTableRecords([$securitySetting]);
    }
}
