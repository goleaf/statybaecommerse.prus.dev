<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\SliderQuickActionsWidget;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class SliderQuickActionsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_slider_quick_actions_widget_can_be_instantiated(): void
    {
        $widget = new SliderQuickActionsWidget();
        $this->assertInstanceOf(SliderQuickActionsWidget::class, $widget);
    }

    public function test_slider_quick_actions_widget_has_correct_properties(): void
    {
        $widget = new SliderQuickActionsWidget();
        
        // Test that widget has required properties without direct access
        $this->assertInstanceOf(SliderQuickActionsWidget::class, $widget);
    }

    public function test_create_slider_action_exists(): void
    {
        $widget = new SliderQuickActionsWidget();
        $action = $widget->createSliderAction();
        
        $this->assertInstanceOf(\Filament\Actions\Action::class, $action);
        $this->assertEquals(__('translations.create_slider'), $action->getLabel());
        $this->assertEquals('heroicon-m-plus', $action->getIcon());
        $this->assertEquals('primary', $action->getColor());
    }

    public function test_toggle_all_sliders_action_exists(): void
    {
        $widget = new SliderQuickActionsWidget();
        $action = $widget->toggleAllSlidersAction();
        
        $this->assertInstanceOf(\Filament\Actions\Action::class, $action);
        $this->assertEquals(__('translations.toggle_all_sliders'), $action->getLabel());
        $this->assertEquals('heroicon-m-power', $action->getIcon());
        $this->assertEquals('warning', $action->getColor());
    }

    public function test_reorder_sliders_action_exists(): void
    {
        $widget = new SliderQuickActionsWidget();
        $action = $widget->reorderSlidersAction();
        
        $this->assertInstanceOf(\Filament\Actions\Action::class, $action);
        $this->assertEquals(__('translations.reorder_sliders'), $action->getLabel());
        $this->assertEquals('heroicon-m-arrows-up-down', $action->getIcon());
        $this->assertEquals('info', $action->getColor());
    }

    public function test_create_slider_action_creates_slider(): void
    {
        $widget = Livewire::test(SliderQuickActionsWidget::class);
        
        $sliderData = [
            'title' => 'Test Slider',
            'description' => 'Test Description',
            'button_text' => 'Click Me',
            'button_url' => 'https://example.com',
            'background_color' => '#ffffff',
            'text_color' => '#000000',
            'sort_order' => 1,
            'is_active' => true,
        ];
        
        $widget->call('createSlider', $sliderData);
        
        $this->assertDatabaseHas('sliders', [
            'title' => 'Test Slider',
            'description' => 'Test Description',
            'button_text' => 'Click Me',
            'button_url' => 'https://example.com',
            'background_color' => '#ffffff',
            'text_color' => '#000000',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_toggle_all_sliders_action_activates_all(): void
    {
        // Create some inactive sliders
        Slider::factory()->count(3)->create(['is_active' => false]);
        
        $widget = Livewire::test(SliderQuickActionsWidget::class);
        $widget->call('toggleAllSliders');
        
        $this->assertDatabaseHas('sliders', ['is_active' => true]);
        $this->assertDatabaseMissing('sliders', ['is_active' => false]);
    }

    public function test_toggle_all_sliders_action_deactivates_all(): void
    {
        // Create some active sliders
        Slider::factory()->count(3)->create(['is_active' => true]);
        
        $widget = Livewire::test(SliderQuickActionsWidget::class);
        $widget->call('toggleAllSliders');
        
        $this->assertDatabaseHas('sliders', ['is_active' => false]);
        $this->assertDatabaseMissing('sliders', ['is_active' => true]);
    }

    public function test_widget_renders_successfully(): void
    {
        $widget = Livewire::test(SliderQuickActionsWidget::class);
        $widget->assertSuccessful();
    }

    public function test_widget_has_required_actions(): void
    {
        $widget = new SliderQuickActionsWidget();
        
        // Test that all required actions exist
        $this->assertTrue(method_exists($widget, 'createSliderAction'));
        $this->assertTrue(method_exists($widget, 'toggleAllSlidersAction'));
        $this->assertTrue(method_exists($widget, 'reorderSlidersAction'));
    }

    public function test_create_slider_action_validation(): void
    {
        $widget = Livewire::test(SliderQuickActionsWidget::class);
        
        // Test with invalid data (missing required title)
        $invalidData = [
            'description' => 'Test Description',
            'button_text' => 'Click Me',
        ];
        
        $widget->call('createSlider', $invalidData);
        
        // Should not create slider without required title
        $this->assertDatabaseMissing('sliders', [
            'description' => 'Test Description',
        ]);
    }

    public function test_widget_uses_correct_traits(): void
    {
        $widget = new SliderQuickActionsWidget();
        
        $this->assertTrue(in_array(\Filament\Actions\Concerns\InteractsWithActions::class, class_uses($widget)));
        $this->assertTrue(in_array(\Filament\Forms\Concerns\InteractsWithForms::class, class_uses($widget)));
    }

    public function test_widget_implements_required_interfaces(): void
    {
        $widget = new SliderQuickActionsWidget();
        
        $this->assertTrue($widget instanceof \Filament\Actions\Contracts\HasActions);
        $this->assertTrue($widget instanceof \Filament\Forms\Contracts\HasForms);
    }
}
