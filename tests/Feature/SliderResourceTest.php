<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SliderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create test sliders
        $this->createTestSliders();
    }

    private function createTestSliders(): void
    {
        // Create active sliders with various features
        Slider::factory()->create([
            'title' => 'Active Slider with Image',
            'is_active' => true,
            'button_text' => 'Learn More',
            'button_url' => 'https://example.com',
            'description' => 'This is a test slider',
            'background_color' => '#ff0000',
            'text_color' => '#ffffff',
        ]);

        Slider::factory()->create([
            'title' => 'Active Slider without Image',
            'is_active' => true,
            'button_text' => null,
            'button_url' => null,
            'description' => null,
        ]);

        Slider::factory()->create([
            'title' => 'Inactive Slider',
            'is_active' => false,
            'button_text' => 'Click Here',
            'button_url' => '/internal-link',
        ]);

        Slider::factory()->create([
            'title' => 'Slider with Background',
            'is_active' => true,
            'background_color' => '#00ff00',
            'text_color' => '#000000',
        ]);
    }

    public function test_can_access_slider_resource_list(): void
    {
        $this->get('/admin/sliders')
            ->assertStatus(200);
    }

    public function test_slider_resource_has_correct_navigation_icon(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Resources\Sliders\SliderResource::class, 'getNavigationIcon'));
    }

    public function test_slider_resource_has_correct_navigation_group(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Resources\Sliders\SliderResource::class, 'getNavigationGroup'));
    }

    public function test_slider_resource_has_correct_model(): void
    {
        $this->assertEquals(Slider::class, \App\Filament\Resources\Sliders\SliderResource::getModel());
    }

    public function test_slider_resource_can_list_sliders(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->assertCanSeeTableRecords(Slider::all());
    }

    public function test_slider_resource_can_create_slider(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\CreateSlider::class)
            ->fillForm([
                'title' => 'New Test Slider',
                'description' => 'Test description',
                'button_text' => 'Click Me',
                'button_url' => 'https://test.com',
                'background_color' => '#ff0000',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sliders', [
            'title' => 'New Test Slider',
            'description' => 'Test description',
            'button_text' => 'Click Me',
            'button_url' => 'https://test.com',
            'background_color' => '#ff0000',
            'text_color' => '#ffffff',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_slider_resource_can_edit_slider(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Slider Title',
                'description' => 'Updated description',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sliders', [
            'id' => $slider->id,
            'title' => 'Updated Slider Title',
            'description' => 'Updated description',
            'is_active' => false,
        ]);
    }

    public function test_slider_resource_can_delete_slider(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('sliders', [
            'id' => $slider->id,
        ]);
    }

    public function test_slider_resource_can_filter_by_status(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(Slider::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(Slider::where('is_active', false)->get());
    }

    public function test_slider_resource_can_search_sliders(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->searchTable('Active Slider with Image')
            ->assertCanSeeTableRecords(Slider::where('title', 'like', '%Active Slider with Image%')->get());
    }

    public function test_slider_resource_can_sort_sliders(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords(Slider::orderBy('title')->get());
    }

    public function test_slider_resource_can_bulk_delete_sliders(): void
    {
        $sliders = Slider::take(2)->get();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->callTableBulkAction('delete', $sliders)
            ->assertHasNoBulkActionErrors();

        foreach ($sliders as $slider) {
            $this->assertDatabaseMissing('sliders', [
                'id' => $slider->id,
            ]);
        }
    }

    public function test_slider_resource_can_toggle_active_status(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->callTableAction('toggle_active', $slider)
            ->assertHasNoTableActionErrors();

        $slider->refresh();
        $this->assertNotEquals($slider->is_active, Slider::first()->is_active);
    }

    public function test_slider_resource_can_duplicate_slider(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->callTableAction('duplicate', $slider)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('sliders', [
            'title' => $slider->title . ' (Copy)',
        ]);
    }

    public function test_slider_resource_validates_required_fields(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\CreateSlider::class)
            ->fillForm([
                'title' => '', // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['title']);
    }

    public function test_slider_resource_validates_url_format(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\CreateSlider::class)
            ->fillForm([
                'title' => 'Test Slider',
                'button_url' => 'invalid-url', // Invalid URL format
            ])
            ->call('create')
            ->assertHasFormErrors(['button_url']);
    }

    public function test_slider_resource_can_upload_image(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->fillForm([
                'image' => \Illuminate\Http\UploadedFile::fake()->image('test.jpg'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($slider->fresh()->hasMedia('slider_images'));
    }

    public function test_slider_resource_can_upload_background(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->fillForm([
                'background' => \Illuminate\Http\UploadedFile::fake()->image('background.jpg'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($slider->fresh()->hasMedia('slider_backgrounds'));
    }

    public function test_slider_resource_can_manage_settings(): void
    {
        $slider = Slider::first();

        Livewire::test(\App\Filament\Resources\Sliders\Pages\EditSlider::class, [
            'record' => $slider->getRouteKey(),
        ])
            ->fillForm([
                'settings' => [
                    'autoplay' => true,
                    'interval' => 5000,
                    'show_indicators' => true,
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $slider->refresh();
        $this->assertIsArray($slider->settings);
        $this->assertTrue($slider->settings['autoplay']);
        $this->assertEquals(5000, $slider->settings['interval']);
        $this->assertTrue($slider->settings['show_indicators']);
    }

    public function test_slider_resource_has_correct_table_columns(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->assertCanSeeTableColumns([
                'title',
                'description',
                'is_active',
                'sort_order',
                'created_at',
            ]);
    }

    public function test_slider_resource_has_correct_form_fields(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\CreateSlider::class)
            ->assertFormExists([
                'title',
                'description',
                'button_text',
                'button_url',
                'background_color',
                'text_color',
                'is_active',
                'sort_order',
            ]);
    }

    public function test_slider_resource_requires_authentication(): void
    {
        auth()->logout();
        
        $this->get('/admin/sliders')
            ->assertRedirect('/admin/login');
    }

    public function test_slider_resource_has_correct_navigation_properties(): void
    {
        $this->assertEquals('heroicon-o-rectangle-stack', \App\Filament\Resources\Sliders\SliderResource::getNavigationIcon());
        $this->assertEquals('Content', \App\Filament\Resources\Sliders\SliderResource::getNavigationGroup());
    }

    public function test_slider_resource_can_export_sliders(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->callTableAction('export')
            ->assertHasNoTableActionErrors();
    }

    public function test_slider_resource_can_import_sliders(): void
    {
        Livewire::test(\App\Filament\Resources\Sliders\Pages\ListSliders::class)
            ->callTableAction('import')
            ->assertHasNoTableActionErrors();
    }
}
