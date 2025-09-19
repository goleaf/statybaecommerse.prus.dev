<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingCategoryResource;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);
    }

    public function test_can_list_system_setting_categories(): void
    {
        SystemSettingCategory::factory()->count(3)->create();

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->assertCanSeeTableRecords(SystemSettingCategory::all());
    }

    public function test_can_create_system_setting_category(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->fillForm([
                'name' => 'Test Category',
                'slug' => 'test-category',
                'description' => 'Test category description',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'icon' => 'heroicon-o-cog-6-tooth',
            'color' => '#3B82F6',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_can_view_system_setting_category(): void
    {
        $category = SystemSettingCategory::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ViewSystemSettingCategory::class, [
            'record' => $category->getKey()
        ])
            ->assertFormSet([
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ]);
    }

    public function test_can_edit_system_setting_category(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Original Name'
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\EditSystemSettingCategory::class, [
            'record' => $category->getKey()
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_system_setting_category(): void
    {
        $category = SystemSettingCategory::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\EditSystemSettingCategory::class, [
            'record' => $category->getKey()
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('system_setting_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_filter_by_parent(): void
    {
        $parent = SystemSettingCategory::factory()->create();
        $child = SystemSettingCategory::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->filterTable('parent_id', $parent->id)
            ->assertCanSeeTableRecords(SystemSettingCategory::where('parent_id', $parent->id)->get())
            ->assertCanNotSeeTableRecords(SystemSettingCategory::whereNull('parent_id')->get());
    }

    public function test_can_filter_by_active_status(): void
    {
        SystemSettingCategory::factory()->create(['is_active' => true]);
        SystemSettingCategory::factory()->create(['is_active' => false]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(SystemSettingCategory::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(SystemSettingCategory::where('is_active', false)->get());
    }

    public function test_can_search_system_setting_categories(): void
    {
        SystemSettingCategory::factory()->create(['name' => 'Test Category 1']);
        SystemSettingCategory::factory()->create(['name' => 'Test Category 2']);
        SystemSettingCategory::factory()->create(['name' => 'Different Category']);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords(SystemSettingCategory::where('name', 'like', '%Test%')->get())
            ->assertCanNotSeeTableRecords(SystemSettingCategory::where('name', 'like', '%Different%')->get());
    }

    public function test_can_duplicate_system_setting_category(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Original Category',
            'slug' => 'original-category'
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->callTableAction('duplicate', $category)
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Original Category (Copy)',
            'slug' => 'original-category-copy',
        ]);
    }

    public function test_can_activate_categories_bulk(): void
    {
        $categories = SystemSettingCategory::factory()->count(3)->create(['is_active' => false]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->callTableBulkAction('activate', $categories)
            ->assertHasNoBulkActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id' => $categories->first()->id,
            'is_active' => true,
        ]);
    }

    public function test_can_deactivate_categories_bulk(): void
    {
        $categories = SystemSettingCategory::factory()->count(3)->create(['is_active' => true]);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->callTableBulkAction('deactivate', $categories)
            ->assertHasNoBulkActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id' => $categories->first()->id,
            'is_active' => false,
        ]);
    }

    public function test_validation_requires_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->fillForm([
                'slug' => 'test-category',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_requires_slug(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->fillForm([
                'name' => 'Test Category',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);
    }

    public function test_validation_requires_unique_slug(): void
    {
        SystemSettingCategory::factory()->create(['slug' => 'existing-slug']);

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->fillForm([
                'name' => 'Test Category',
                'slug' => 'existing-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_navigation_group_is_system(): void
    {
        $this->assertEquals(
            NavigationGroup::System->value,
            SystemSettingCategoryResource::getNavigationGroup()
        );
    }

    public function test_navigation_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_setting_categories.title'),
            SystemSettingCategoryResource::getNavigationLabel()
        );
    }

    public function test_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_setting_categories.single'),
            SystemSettingCategoryResource::getModelLabel()
        );
    }

    public function test_plural_model_label_is_translated(): void
    {
        $this->assertEquals(
            __('system_setting_categories.plural'),
            SystemSettingCategoryResource::getPluralModelLabel()
        );
    }

    public function test_record_title_attribute_is_name(): void
    {
        $this->assertEquals('name', SystemSettingCategoryResource::getRecordTitleAttribute());
    }

    public function test_navigation_sort_is_two(): void
    {
        $this->assertEquals(2, SystemSettingCategoryResource::getNavigationSort());
    }

    public function test_form_sections_are_organized(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->assertFormExists()
            ->assertFormFieldExists('name')
            ->assertFormFieldExists('slug')
            ->assertFormFieldExists('description')
            ->assertFormFieldExists('icon')
            ->assertFormFieldExists('color')
            ->assertFormFieldExists('parent_id')
            ->assertFormFieldExists('sort_order')
            ->assertFormFieldExists('is_active');
    }

    public function test_table_columns_are_configured(): void
    {
        SystemSettingCategory::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->assertCanSeeTableColumns([
                'name',
                'slug',
                'description',
                'icon',
                'color',
                'parent.name',
                'settings_count',
                'active_settings_count',
                'is_active',
            ]);
    }

    public function test_table_filters_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->assertCanSeeTableFilters([
                'parent_id',
                'is_active',
            ]);
    }

    public function test_table_actions_are_configured(): void
    {
        $category = SystemSettingCategory::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->assertCanSeeTableActions([
                'view',
                'edit',
                'duplicate',
            ]);
    }

    public function test_bulk_actions_are_configured(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class)
            ->assertCanSeeBulkActions([
                'delete',
                'activate',
                'deactivate',
            ]);
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class)
            ->fillForm([
                'name' => 'Test Category Name',
            ])
            ->assertFormSet([
                'slug' => 'test-category-name'
            ]);
    }
}
