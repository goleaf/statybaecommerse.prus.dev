<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SliderTranslationResource\Pages\CreateSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\EditSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Filament\Resources\SliderTranslationResource\Pages\ViewSliderTranslation;
use App\Models\Slider;
use App\Models\SliderTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SliderTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Slider $slider;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel is available for the resource tests.
        $this->resolveAdminPanel();

        // Normalise the locale so translated assertions return deterministic strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create an administrator account that can access the Filament panel.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Seed a baseline slider for translation relationships and authenticate as the admin.
        $this->slider = Slider::factory()->create([
            'title' => 'Homepage hero',
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_existing_translations(): void
    {
        // Arrange: persist a translation so the listing has a record to display.
        $translation = SliderTranslation::factory()->for($this->slider)->create([
            'locale' => 'en',
            'title'  => 'Welcome headline',
        ]);

        // Act & Assert: load the list page and confirm the seeded record is visible in the table.
        Livewire::test(ListSliderTranslations::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$translation])
            ->assertSee($translation->title);
    }

    public function test_can_create_slider_translation_via_filament_form(): void
    {
        // Arrange: describe the translation payload administrators would enter in the create form.
        $formData = [
            'slider_id'   => $this->slider->getKey(),
            'locale'      => 'de',
            'title'       => 'Hero auf Deutsch',
            'description' => 'Beschreibung für die Startseite',
            'button_text' => 'Jetzt entdecken',
        ];

        // Act: submit the create form with the prepared payload.
        Livewire::test(CreateSliderTranslation::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert: verify the new translation has been persisted with the expected attributes.
        $this->assertDatabaseHas('slider_translations', [
            'slider_id' => $this->slider->getKey(),
            'locale'    => 'de',
            'title'     => 'Hero auf Deutsch',
        ]);
    }

    public function test_can_edit_existing_slider_translation(): void
    {
        // Arrange: seed an original translation that will be updated through the edit form.
        $translation = SliderTranslation::factory()->for($this->slider)->create([
            'locale' => 'lt',
            'title'  => 'Pradinis pavadinimas',
        ]);

        $updatedFormData = [
            'slider_id'   => $this->slider->getKey(),
            'locale'      => 'lt',
            'title'       => 'Atnaujintas pavadinimas',
            'description' => 'Atnaujintas aprašymas',
            'button_text' => 'Peržiūrėti dabar',
        ];

        // Act: update the translation via the Filament edit page.
        Livewire::test(EditSliderTranslation::class, ['record' => $translation->getRouteKey()])
            ->fillForm($updatedFormData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert: confirm the database reflects the edited translation content.
        $this->assertDatabaseHas('slider_translations', [
            'id'          => $translation->getKey(),
            'title'       => 'Atnaujintas pavadinimas',
            'description' => 'Atnaujintas aprašymas',
            'button_text' => 'Peržiūrėti dabar',
        ]);
    }

    public function test_table_filters_by_slider_and_locale(): void
    {
        // Arrange: create translations spanning multiple sliders and locales.
        $secondarySlider = Slider::factory()->create([
            'title' => 'Secondary hero',
        ]);

        $english = SliderTranslation::factory()->for($this->slider)->create([
            'locale' => 'en',
            'title'  => 'English hero copy',
        ]);

        $lithuanian = SliderTranslation::factory()->for($secondarySlider)->create([
            'locale' => 'lt',
            'title'  => 'Lietuviškas tekstas',
        ]);

        // Act & Assert: apply the slider and locale filters and ensure only the matching record remains visible.
        Livewire::test(ListSliderTranslations::class)
            ->call('loadTable')
            ->filterTable('slider', $this->slider->getKey())
            ->filterTable('locale', 'en')
            ->assertCanSeeTableRecords([$english])
            ->assertCanNotSeeTableRecords([$lithuanian]);
    }

    public function test_view_page_renders_translation_details(): void
    {
        // Arrange: create a translation whose details should appear on the view page.
        $translation = SliderTranslation::factory()->for($this->slider)->create([
            'locale' => 'es',
            'title'  => 'Bienvenido a Statybae',
        ]);

        // Act & Assert: render the view page and confirm key attributes are displayed to the administrator.
        Livewire::test(ViewSliderTranslation::class, ['record' => $translation->getRouteKey()])
            ->assertSee($translation->title)
            ->assertSee($translation->locale);
    }
}
