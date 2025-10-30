<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\CreateSystemSettingCategoryTranslation;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\EditSystemSettingCategoryTranslation;
use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingCategoryTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel guard before mounting Livewire components.
        $this->resolveAdminPanel();

        // Normalise locale-dependent formatting to keep assertions stable across environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed roles so the administrator inherits the full permission matrix.
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->admin->assignRole('administrator');

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Marketing',
            'slug' => 'marketing',
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_existing_translations(): void
    {
        // Create a pair of translations so the table list has concrete data to display.
        $translations = SystemSettingCategoryTranslation::factory()
            ->count(2)
            ->forCategory($this->category)
            ->create([
                'name'        => 'Localized name',
                'description' => 'Localized description copy for audits.',
            ]);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($translations);
    }

    public function test_admin_can_create_translation(): void
    {
        // Submit a translation payload mirroring the resource form fields.
        Livewire::test(CreateSystemSettingCategoryTranslation::class)
            ->fillForm([
                'system_setting_category_id' => $this->category->id,
                'locale'                     => 'lt',
                'name'                       => 'Lietuviškas pavadinimas',
                'description'                => 'Išsamus kategorijos aprašymas lietuvių kalba.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_category_translations', [
            'system_setting_category_id' => $this->category->id,
            'locale'                     => 'lt',
            'name'                       => 'Lietuviškas pavadinimas',
        ]);
    }

    public function test_admin_can_update_translation(): void
    {
        // Seed an English translation to exercise the edit workflow.
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($this->category)
            ->create([
                'locale' => 'en',
                'name'   => 'Initial headline',
            ]);

        Livewire::test(EditSystemSettingCategoryTranslation::class, ['record' => $translation->getKey()])
            ->fillForm([
                'system_setting_category_id' => $this->category->id,
                'locale'                     => 'en',
                'name'                       => 'Updated headline',
                'description'                => 'Refined copy for the marketing category.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_category_translations', [
            'id'     => $translation->id,
            'name'   => 'Updated headline',
            'locale' => 'en',
        ]);
    }

    public function test_duplicate_action_creates_copy(): void
    {
        // Prepare a Lithuanian translation that will be duplicated through the table action.
        $translation = SystemSettingCategoryTranslation::factory()
            ->forCategory($this->category)
            ->create([
                'locale' => 'lt',
                'name'   => 'Pirminis pavadinimas',
            ]);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $translation)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('system_setting_category_translations', [
            'system_setting_category_id' => $this->category->id,
            'locale'                     => 'lt',
            'name'                       => 'Pirminis pavadinimas (Copy)',
        ]);
    }

    public function test_bulk_duplicate_for_locale_generates_new_records(): void
    {
        // Start with only Lithuanian translations so duplicating into English produces fresh rows.
        $lithuanian = SystemSettingCategoryTranslation::factory()
            ->count(2)
            ->forCategory($this->category)
            ->state([
                'locale' => 'lt',
            ])
            ->sequence([
                'name' => 'Kategorija A',
            ], [
                'name' => 'Kategorija B',
            ])
            ->create();

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->callTableBulkAction('duplicate_for_locale', $lithuanian, data: ['target_locale' => 'en'])
            ->assertHasNoBulkActionErrors();

        $this->assertDatabaseCount('system_setting_category_translations', 4);
        $this->assertDatabaseHas('system_setting_category_translations', [
            'system_setting_category_id' => $this->category->id,
            'locale'                     => 'en',
            'name'                       => 'Kategorija A',
        ]);
    }

    public function test_filters_can_scope_by_category_and_locale(): void
    {
        // Provision translations across locales to exercise both filter dropdowns.
        $english = SystemSettingCategoryTranslation::factory()
            ->forCategory($this->category)
            ->create([
                'locale' => 'en',
                'name'   => 'English name',
            ]);

        $otherCategory = SystemSettingCategory::factory()->create([
            'name' => 'Payments',
            'slug' => 'payments',
        ]);

        $lithuanian = SystemSettingCategoryTranslation::factory()
            ->forCategory($otherCategory)
            ->create([
                'locale' => 'lt',
                'name'   => 'Lietuviškas pavadinimas',
            ]);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->filterTable('system_setting_category_id', (string) $this->category->id)
            ->assertCanSeeTableRecords([$english])
            ->assertCanNotSeeTableRecords([$lithuanian])
            ->filterTable('locale', 'lt')
            ->assertCanSeeTableRecords([$lithuanian])
            ->assertCanNotSeeTableRecords([$english]);
    }

    public function test_completeness_filter_surfaces_incomplete_records(): void
    {
        // Create a complete translation and an intentionally incomplete counterpart for the filter check.
        $complete = SystemSettingCategoryTranslation::factory()
            ->forCategory($this->category)
            ->create([
                'locale'      => 'en',
                'name'        => 'Complete entry',
                'description' => 'Fully documented category copy.',
            ]);

        $incomplete = SystemSettingCategoryTranslation::factory()
            ->forCategory($this->category)
            ->create([
                'locale'      => 'lt',
                'name'        => null,
                'description' => 'Missing name field to trigger incomplete filter.',
            ]);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->filterTable('completeness', 'incomplete')
            ->assertCanSeeTableRecords([$incomplete])
            ->assertCanNotSeeTableRecords([$complete])
            ->filterTable('completeness', 'complete')
            ->assertCanSeeTableRecords([$complete])
            ->assertCanNotSeeTableRecords([$incomplete]);
    }
}

