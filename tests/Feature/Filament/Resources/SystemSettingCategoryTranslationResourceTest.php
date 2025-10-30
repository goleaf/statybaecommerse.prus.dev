<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages\ListSystemSettingCategoryTranslations;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingCategoryTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the admin panel routes and resources are fully registered for Filament interactions.
        $this->resolveAdminPanel();

        // Lock localisation to English so translation-dependent assertions remain stable across environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Impersonate an administrator user so protected Filament resources can be accessed in Livewire tests.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_translation_with_parent_category(): void
    {
        // Create a category and its English translation to validate table rendering for translatable resources.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Notifications',
            'slug' => 'notifications',
        ]);

        $translation = SystemSettingCategoryTranslation::factory()
            ->english()
            ->for($category, 'systemSettingCategory')
            ->create([
                'name'        => 'Notifications',
                'description' => 'Notification system configuration.',
            ]);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$translation]);
    }

    public function test_locale_filter_only_shows_requested_language(): void
    {
        // Seed translations for multiple locales so the locale filter can be exercised meaningfully.
        $category = SystemSettingCategory::factory()->create();

        $english = SystemSettingCategoryTranslation::factory()
            ->english()
            ->for($category, 'systemSettingCategory')
            ->create(['name' => 'General Settings']);

        $lithuanian = SystemSettingCategoryTranslation::factory()
            ->lithuanian()
            ->for($category, 'systemSettingCategory')
            ->create(['name' => 'Bendri nustatymai']);

        Livewire::test(ListSystemSettingCategoryTranslations::class)
            ->call('loadTable')
            ->filterTable('locale', 'en')
            ->assertCanSeeTableRecords([$english])
            ->assertCanNotSeeTableRecords([$lithuanian]);
    }
}
