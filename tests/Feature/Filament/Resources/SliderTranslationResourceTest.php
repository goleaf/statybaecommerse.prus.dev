<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SliderTranslationResource\Pages\CreateSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\EditSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Models\Slider;
use App\Models\SliderTranslation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SliderTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the shared roles so Filament policies resolve the expected permissions.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision an administrator account that mirrors the Filament guard defaults.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Grant the administrator role so `canViewAny` checks pass in the resource.
        $this->admin->assignRole('administrator');
    }

    public function test_admin_can_list_slider_translations(): void
    {
        // Create a pair of translations so the table has data to render.
        $translations = SliderTranslation::factory()->count(2)->create();

        $this->actingAs($this->admin);

        // Load the list page and verify each translation record appears in the table output.
        Livewire::test(ListSliderTranslations::class)
            ->assertCanSeeTableRecords($translations);
    }

    public function test_admin_can_create_slider_translation(): void
    {
        $this->actingAs($this->admin);

        // Prepare a slider so the form relationship dropdown has a valid target.
        $slider = Slider::factory()->create();

        // Submit the creation form with localized copy and ensure persistence succeeds.
        Livewire::test(CreateSliderTranslation::class)
            ->fillForm([
                'slider_id'   => $slider->id,
                'locale'      => 'en',
                'title'       => 'Homepage Hero',
                'description' => 'Primary hero messaging for the landing slider.',
                'button_text' => 'Shop now',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Confirm the translation row was stored exactly as requested.
        $this->assertDatabaseHas('slider_translations', [
            'slider_id' => $slider->id,
            'locale'    => 'en',
            'title'     => 'Homepage Hero',
        ]);
    }

    public function test_admin_can_update_slider_translation(): void
    {
        // Seed an existing translation to modify through the edit form.
        $translation = SliderTranslation::factory()->create([
            'title' => 'Legacy headline',
        ]);

        $this->actingAs($this->admin);

        // Update the translation text and ensure the Livewire form validates successfully.
        Livewire::test(EditSliderTranslation::class, ['record' => $translation->getRouteKey()])
            ->fillForm([
                'slider_id'   => $translation->slider_id,
                'locale'      => $translation->locale,
                'title'       => 'Updated headline',
                'description' => 'Revised messaging for the marketing slider.',
                'button_text' => 'Discover more',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Verify the persisted data reflects the updated copy.
        $this->assertDatabaseHas('slider_translations', [
            'id'          => $translation->id,
            'title'       => 'Updated headline',
            'description' => 'Revised messaging for the marketing slider.',
            'button_text' => 'Discover more',
        ]);
    }

    public function test_admin_can_delete_slider_translation(): void
    {
        // Generate a translation that will be removed through the resource delete action.
        $translation = SliderTranslation::factory()->create();

        $this->actingAs($this->admin);

        // Call the delete action exposed on the edit page to remove the translation row.
        Livewire::test(EditSliderTranslation::class, ['record' => $translation->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        // Ensure the database no longer contains the deleted translation.
        $this->assertDatabaseMissing('slider_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_table_filters_by_locale_and_slider(): void
    {
        $this->actingAs($this->admin);

        // Create translations across locales and sliders to exercise both filters.
        $englishSlider = Slider::factory()->create(['name' => 'Hero Slider']);
        $lithuanianSlider = Slider::factory()->create(['name' => 'Promo Slider']);

        $english = SliderTranslation::factory()->create([
            'slider_id' => $englishSlider->id,
            'locale'    => 'en',
            'title'     => 'English headline',
        ]);

        $lithuanian = SliderTranslation::factory()->create([
            'slider_id' => $lithuanianSlider->id,
            'locale'    => 'lt',
            'title'     => 'Lietuviškas antraštė',
        ]);

        // Confirm the locale filter narrows results to the requested language.
        Livewire::test(ListSliderTranslations::class)
            ->filterTable('locale', 'en')
            ->assertCanSeeTableRecords([$english])
            ->assertCanNotSeeTableRecords([$lithuanian])
            // Follow up by scoping to a specific slider to verify the relationship filter.
            ->filterTable('slider', (string) $lithuanianSlider->id)
            ->assertCanSeeTableRecords([$lithuanian])
            ->assertCanNotSeeTableRecords([$english]);
    }
}
