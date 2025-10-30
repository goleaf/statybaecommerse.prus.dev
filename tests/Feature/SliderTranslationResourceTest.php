<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SliderTranslationResource;
use App\Filament\Resources\SliderTranslationResource\Pages\CreateSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\EditSliderTranslation;
use App\Filament\Resources\SliderTranslationResource\Pages\ListSliderTranslations;
use App\Models\Slider;
use App\Models\SliderTranslation;
use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SliderTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Slider $slider;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate an administrator so the Filament guard passes authorization checks.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
        $this->actingAs($this->adminUser);

        // Provision a base slider that translations can reference in relational assertions.
        $this->slider = Slider::factory()->create([
            'title'       => 'Primary Slider',
            'description' => 'Hero banner slider',
            'is_active'   => true,
        ]);
    }

    public function test_navigation_configuration_aligns_with_registry(): void
    {
        // Validate the navigation metadata so the panel sidebar renders as expected.
        self::assertSame(
            Nav::iconForResource(SliderTranslationResource::class),
            SliderTranslationResource::getNavigationIcon(),
        );
        self::assertSame(
            Nav::groupForResource(SliderTranslationResource::class),
            SliderTranslationResource::getNavigationGroup(),
        );
        self::assertSame(
            Nav::sortForResource(SliderTranslationResource::class),
            SliderTranslationResource::getNavigationSort(),
        );
        self::assertSame(
            __('admin.slider_translations.navigation_label'),
            SliderTranslationResource::getNavigationLabel(),
        );
    }

    public function test_list_page_renders_translations(): void
    {
        // Create a pair of translations to ensure the table renders multilingual content.
        $lithuanian = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'lt',
            'title'     => 'Pagrindinis baneris',
        ]);
        $english = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'en',
            'title'     => 'Primary Banner',
        ]);

        Livewire::test(ListSliderTranslations::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$lithuanian, $english]);
    }

    public function test_create_form_stores_translation(): void
    {
        // Drive the create flow with deterministic copy to guarantee assertions remain stable.
        Livewire::test(CreateSliderTranslation::class)
            ->fillForm([
                'slider_id'    => $this->slider->id,
                'locale'       => 'de',
                'title'        => 'Startseiten Banner',
                'description'  => 'Beschreibung für die deutschsprachige Folie.',
                'button_text'  => 'Mehr erfahren',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('slider_translations', [
            'slider_id' => $this->slider->id,
            'locale'    => 'de',
            'title'     => 'Startseiten Banner',
        ]);
    }

    public function test_edit_form_updates_copy(): void
    {
        $translation = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'en',
            'title'     => 'Original Title',
        ]);

        // Update the translation to confirm the form persists localized text.
        Livewire::test(EditSliderTranslation::class, [
            'record' => $translation->getRouteKey(),
        ])
            ->fillForm([
                'title'       => 'Updated Title',
                'description' => 'Updated description copy.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('slider_translations', [
            'id'          => $translation->id,
            'title'       => 'Updated Title',
            'description' => 'Updated description copy.',
        ]);
    }

    public function test_filters_scope_by_locale(): void
    {
        $english = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'en',
        ]);
        $spanish = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'es',
        ]);

        // Apply the locale filter so only the targeted translation remains visible.
        Livewire::test(ListSliderTranslations::class)
            ->call('loadTable')
            ->filterTable('locale', 'es')
            ->assertCanSeeTableRecords([$spanish])
            ->assertCanNotSeeTableRecords([$english]);
    }

    public function test_filters_scope_by_slider_relation(): void
    {
        $secondarySlider = Slider::factory()->create([
            'title'     => 'Secondary Slider',
            'is_active' => true,
        ]);

        $primaryTranslation = SliderTranslation::factory()->create([
            'slider_id' => $this->slider->id,
            'locale'    => 'lt',
        ]);
        $secondaryTranslation = SliderTranslation::factory()->create([
            'slider_id' => $secondarySlider->id,
            'locale'    => 'en',
        ]);

        // Ensure the relationship filter constrains records to the selected slider identifier.
        Livewire::test(ListSliderTranslations::class)
            ->call('loadTable')
            ->filterTable('slider', $this->slider->id)
            ->assertCanSeeTableRecords([$primaryTranslation])
            ->assertCanNotSeeTableRecords([$secondaryTranslation]);
    }
}
