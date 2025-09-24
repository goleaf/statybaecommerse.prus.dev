<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

final class SliderRecommendations extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.pages.slider-analytics.widgets.slider-recommendations';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        $sliders = $query->get();

        return [
            'recommendations' => $this->generateRecommendations($sliders),
            'totalSliders' => $sliders->count(),
            'activeSliders' => $sliders->where('is_active', true)->count(),
        ];
    }

    private function generateRecommendations($sliders): array
    {
        $recommendations = [];

        // Check for sliders without images
        $slidersWithoutImages = $sliders->filter(fn ($slider) => ! $slider->hasMedia('slider_images'));
        if ($slidersWithoutImages->count() > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-camera',
                'title' => 'Add Images to Sliders',
                'description' => "{$slidersWithoutImages->count()} sliders don't have images. Consider adding visual content to improve engagement.",
                'action' => 'Add images to improve visual appeal',
                'count' => $slidersWithoutImages->count(),
            ];
        }

        // Check for sliders without buttons
        $slidersWithoutButtons = $sliders->filter(fn ($slider) => empty($slider->button_text) || empty($slider->button_url));
        if ($slidersWithoutButtons->count() > 0) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-cursor-arrow-rays',
                'title' => 'Add Call-to-Action Buttons',
                'description' => "{$slidersWithoutButtons->count()} sliders don't have buttons. Add CTAs to drive user engagement.",
                'action' => 'Add buttons to increase conversions',
                'count' => $slidersWithoutButtons->count(),
            ];
        }

        // Check for sliders without descriptions
        $slidersWithoutDescriptions = $sliders->filter(fn ($slider) => empty($slider->description));
        if ($slidersWithoutDescriptions->count() > 0) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-document-text',
                'title' => 'Add Descriptions',
                'description' => "{$slidersWithoutDescriptions->count()} sliders don't have descriptions. Add compelling copy to explain your offerings.",
                'action' => 'Add descriptions to improve messaging',
                'count' => $slidersWithoutDescriptions->count(),
            ];
        }

        // Check for inactive sliders
        $inactiveSliders = $sliders->where('is_active', false);
        if ($inactiveSliders->count() > 0) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-eye-slash',
                'title' => 'Activate Inactive Sliders',
                'description' => "{$inactiveSliders->count()} sliders are inactive. Consider activating them to increase content variety.",
                'action' => 'Activate sliders to show more content',
                'count' => $inactiveSliders->count(),
            ];
        }

        // Check for sliders without custom styling
        $slidersWithoutStyling = $sliders->filter(fn ($slider) => empty($slider->background_color) && empty($slider->text_color));
        if ($slidersWithoutStyling->count() > 0) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-paint-brush',
                'title' => 'Customize Colors',
                'description' => "{$slidersWithoutStyling->count()} sliders use default colors. Customize colors to match your brand.",
                'action' => 'Add custom colors for brand consistency',
                'count' => $slidersWithoutStyling->count(),
            ];
        }

        // Check for old sliders
        $oldSliders = $sliders->filter(fn ($slider) => $slider->created_at->diffInDays(now()) > 90);
        if ($oldSliders->count() > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-clock',
                'title' => 'Update Old Content',
                'description' => "{$oldSliders->count()} sliders are older than 90 days. Consider updating content to keep it fresh.",
                'action' => 'Update old sliders with fresh content',
                'count' => $oldSliders->count(),
            ];
        }

        // Positive recommendations
        $slidersWithAllFeatures = $sliders->filter(function ($slider) {
            return $slider->hasMedia('slider_images') &&
                ! empty($slider->button_text) &&
                ! empty($slider->button_url) &&
                ! empty($slider->description) &&
                $slider->is_active;
        });

        if ($slidersWithAllFeatures->count() > 0) {
            $recommendations[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'title' => 'Excellent Slider Quality',
                'description' => "{$slidersWithAllFeatures->count()} sliders have all recommended features. Great job!",
                'action' => 'Keep up the excellent work',
                'count' => $slidersWithAllFeatures->count(),
            ];
        }

        return $recommendations;
    }
}
