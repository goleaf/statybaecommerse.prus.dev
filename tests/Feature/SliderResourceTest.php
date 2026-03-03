<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\SliderResource;
use App\Models\AdminUser;
use App\Models\Slider;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

final class SliderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected AdminUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('media');

        $this->user = AdminUser::factory()->create();
        $this->actingAs($this->user, 'admin');

        $this->createTestSliders();
    }

    private function createTestSliders(): void
    {
        $slider1 = Slider::factory()->create([
            'title'            => 'Active Slider with Image',
            'is_active'        => true,
            'button_text'      => 'Learn More',
            'button_url'       => 'https://example.com',
            'description'      => 'This is a test slider',
            'background_color' => '#ff0000',
            'text_color'       => '#ffffff',
        ]);
        $this->attachImage($slider1);

        $slider2 = Slider::factory()->create([
            'title'       => 'Active Slider without Image',
            'is_active'   => true,
            'button_text' => null,
            'button_url'  => null,
            'description' => null,
        ]);
        $this->attachImage($slider2);

        $slider3 = Slider::factory()->create([
            'title'       => 'Inactive Slider',
            'is_active'   => false,
            'button_text' => 'Click Here',
            'button_url'  => '/products',
        ]);
        $this->attachImage($slider3);

        $slider4 = Slider::factory()->create([
            'title'            => 'Slider with Background',
            'is_active'        => true,
            'background_color' => '#00ff00',
            'text_color'       => '#000000',
        ]);
        $this->attachImage($slider4);
    }

    private function attachImage(Slider $slider): void
    {
        try {
            $slider->addMedia(UploadedFile::fake()->image('slider.jpg'))
                ->toMediaCollection('slider_images');
        } catch (Exception) {
            // Ignore media edge cases in SQLite test runs.
        }
    }

    public function test_can_access_slider_resource_list(): void
    {
        $this->get('/admin/sliders')->assertSuccessful();
    }

    public function test_slider_resource_has_correct_navigation_group(): void
    {
        $this->assertSame(NavigationGroup::Content->label(), SliderResource::getNavigationGroup());
    }

    public function test_slider_resource_has_correct_model(): void
    {
        $this->assertSame(Slider::class, SliderResource::getModel());
    }

    public function test_slider_resource_has_expected_header_actions(): void
    {
        Livewire::test(ListSliders::class)
            ->assertActionExists('create')
            ->assertActionExists('settings')
            ->assertActionExists('toggleAllSliders');
    }

    public function test_slider_resource_has_expected_table_and_bulk_actions(): void
    {
        Livewire::test(ListSliders::class)
            ->assertTableActionExists('toggleSlider')
            ->assertTableActionExists('edit')
            ->assertTableActionExists('delete')
            ->assertTableActionExists('replicate')
            ->assertTableBulkActionExists('delete');
    }

    public function test_slider_resource_can_list_sliders(): void
    {
        Livewire::test(ListSliders::class)
            ->assertCanSeeTableRecords(Slider::all());
    }

    public function test_slider_resource_can_create_slider_with_searchable_button_url(): void
    {
        $slug = 'new-test-slider-' . uniqid();

        Livewire::test(CreateSlider::class)
            ->fillForm([
                'title'            => 'New Test Slider',
                'slug'             => $slug,
                'description'      => 'Test description',
                'button_text'      => 'Click Me',
                'button_url'       => '/products',
                'background_color' => '#ff0000',
                'text_color'       => '#ffffff',
                'is_active'        => true,
                'sort_order'       => 1,
                'slides'           => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sliders', [
            'slug'       => $slug,
            'button_url' => '/products',
        ]);
    }

    public function test_slider_resource_can_edit_slider(): void
    {
        $slider = Slider::query()->firstOrFail();
        $slider->update(['slug' => 'editable-slider-' . $slider->id]);

        $updatedSlug = 'updated-slider-' . $slider->id;

        Livewire::test(EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->fillForm([
                'title'            => 'Updated Slider Title',
                'slug'             => $updatedSlug,
                'description'      => 'Updated description',
                'button_text'      => 'Updated CTA',
                'button_url'       => '/updated-products',
                'background_color' => '#112233',
                'text_color'       => '#ffffff',
                'is_active'        => false,
                'sort_order'       => 25,
                'slides'           => [],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sliders', [
            'id'          => $slider->id,
            'title'       => 'Updated Slider Title',
            'slug'        => $updatedSlug,
            'description' => '<p>Updated description</p>',
            'button_url'  => '/updated-products',
            'is_active'   => false,
            'sort_order'  => 25,
        ]);
    }

    public function test_slider_resource_can_delete_slider(): void
    {
        $slider = Slider::query()->firstOrFail();

        Livewire::test(ListSliders::class)
            ->callTableAction('delete', $slider)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('sliders', [
            'id' => $slider->id,
        ]);
    }

    public function test_slider_resource_can_filter_by_status(): void
    {
        Livewire::test(ListSliders::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(Slider::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(Slider::where('is_active', false)->get());
    }

    public function test_slider_resource_can_search_sliders(): void
    {
        Livewire::test(ListSliders::class)
            ->searchTable('Active Slider with Image')
            ->assertCanSeeTableRecords(Slider::where('title', 'like', '%Active Slider with Image%')->get());
    }

    public function test_slider_resource_can_sort_sliders(): void
    {
        Livewire::test(ListSliders::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords(Slider::orderBy('title')->get());
    }

    public function test_slider_resource_can_toggle_active_status(): void
    {
        $slider = Slider::query()->where('is_active', true)->firstOrFail();
        $isActiveBeforeToggle = $slider->is_active;

        Livewire::test(ListSliders::class)
            ->callTableAction('toggleSlider', $slider)
            ->assertHasNoTableActionErrors();

        $this->assertNotSame($isActiveBeforeToggle, $slider->fresh()->is_active);
    }

    public function test_slider_resource_can_duplicate_slider(): void
    {
        $slider = Slider::query()->whereNull('slug')->firstOrFail();
        $initialCount = Slider::count();

        Livewire::test(ListSliders::class)
            ->callTableAction('replicate', $slider)
            ->assertHasNoTableActionErrors();

        $this->assertSame($initialCount + 1, Slider::count());
        $this->assertDatabaseHas('sliders', [
            'title' => $slider->title . ' (Copy)',
        ]);
    }

    public function test_slider_resource_can_bulk_delete_sliders(): void
    {
        $sliders = Slider::query()->take(2)->get();

        Livewire::test(ListSliders::class)
            ->callTableBulkAction('delete', $sliders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($sliders as $slider) {
            $this->assertDatabaseMissing('sliders', [
                'id' => $slider->id,
            ]);
        }
    }

    public function test_slider_resource_can_toggle_all_sliders_to_inactive_when_active_majority(): void
    {
        $this->assertGreaterThan(
            Slider::query()->where('is_active', false)->count(),
            Slider::query()->where('is_active', true)->count()
        );

        Livewire::test(ListSliders::class)
            ->callAction('toggleAllSliders')
            ->assertHasNoActionErrors();

        $this->assertSame(0, Slider::query()->where('is_active', true)->count());
    }

    public function test_slider_resource_can_toggle_all_sliders_to_active_when_inactive_majority(): void
    {
        Slider::query()->update(['is_active' => false]);

        Livewire::test(ListSliders::class)
            ->callAction('toggleAllSliders')
            ->assertHasNoActionErrors();

        $this->assertSame(Slider::count(), Slider::query()->where('is_active', true)->count());
    }

    public function test_slider_resource_can_submit_settings_action(): void
    {
        Livewire::test(ListSliders::class)
            ->callAction('settings', [
                'auto_optimize_images' => false,
                'default_animation'    => 'zoom',
                'default_duration'     => 4000,
            ])
            ->assertHasNoActionErrors();
    }

    public function test_slider_resource_has_correct_table_columns(): void
    {
        Livewire::test(ListSliders::class)
            ->assertCanSeeTableColumns([
                'title',
                'sort_order',
                'created_at',
            ]);
    }

    public function test_slider_resource_has_correct_form_fields(): void
    {
        Livewire::test(CreateSlider::class)
            ->assertFormFieldExists('title')
            ->assertFormFieldExists('slug')
            ->assertFormFieldExists('description')
            ->assertFormFieldExists('button_text')
            ->assertFormFieldExists('background_color')
            ->assertFormFieldExists('text_color')
            ->assertFormFieldExists('is_active')
            ->assertFormFieldExists('sort_order');
    }

    public function test_slider_resource_requires_authentication(): void
    {
        auth('admin')->logout();

        $this->get('/admin/sliders')->assertRedirect('/admin/login');
    }
}
