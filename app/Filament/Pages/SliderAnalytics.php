<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

/**
 * Slider Analytics Dashboard
 *
 * Features:
 * - Comprehensive slider performance analytics
 * - Visual charts and statistics
 * - Date range filtering
 * - Slider performance comparison
 * - Click-through rate analysis
 * - Engagement metrics
 * - Export capabilities
 * - Real-time data updates
 */
final class SliderAnalytics extends BaseDashboard
{
    use HasFiltersAction;
    use InteractsWithPageFilters;

    protected static ?string $title = 'Slider Analytics';

    protected static ?string $navigationLabel = 'Slider Analytics';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 3;

    protected static string $routePath = 'slider-analytics';

    protected int|string|array $columnSpan = 'full';

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Filter Analytics')
                ->icon('heroicon-o-funnel')
                ->components([
                    DatePicker::make('startDate')
                        ->label('Start Date')
                        ->default(now()->subDays(30))
                        ->displayFormat('Y-m-d')
                        ->helperText('Select the start date for analytics'),
                    DatePicker::make('endDate')
                        ->label('End Date')
                        ->default(now())
                        ->displayFormat('Y-m-d')
                        ->helperText('Select the end date for analytics'),
                    Select::make('sliderId')
                        ->label('Specific Slider')
                        ->options(Slider::all()->pluck('title', 'id'))
                        ->searchable()
                        ->placeholder('All Sliders')
                        ->helperText('Filter by specific slider'),
                    Select::make('status')
                        ->label('Status Filter')
                        ->options([
                            'all' => 'All Sliders',
                            'active' => 'Active Only',
                            'inactive' => 'Inactive Only',
                        ])
                        ->default('all')
                        ->helperText('Filter by slider status'),
                ]),
            Action::make('export')
                ->label('Export Analytics')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $this->exportAnalytics();
                }),
            Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    $this->refreshData();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SliderAnalytics\Widgets\SliderOverviewStats::class,
            SliderAnalytics\Widgets\SliderPerformanceChart::class,
            SliderAnalytics\Widgets\SliderEngagementMetrics::class,
            SliderAnalytics\Widgets\TopPerformingSliders::class,
            SliderAnalytics\Widgets\SliderClickThroughRates::class,
            SliderAnalytics\Widgets\SliderViewsTimeline::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SliderAnalytics\Widgets\SliderComparisonTable::class,
            SliderAnalytics\Widgets\SliderRecommendations::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    protected function exportAnalytics(): void
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn(Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn(Builder $query) => $query->where('is_active', $status === 'active'));

        $sliders = $query->with(['media', 'translations'])->get();

        $analyticsData = $sliders->map(function (Slider $slider) {
            return [
                'ID' => $slider->id,
                'Title' => $slider->title,
                'Status' => $slider->is_active ? 'Active' : 'Inactive',
                'Created At' => $slider->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $slider->updated_at->format('Y-m-d H:i:s'),
                'Sort Order' => $slider->sort_order,
                'Has Image' => $slider->hasMedia('slider_images') ? 'Yes' : 'No',
                'Has Background' => $slider->hasMedia('slider_backgrounds') ? 'Yes' : 'No',
                'Button Text' => $slider->button_text,
                'Button URL' => $slider->button_url,
                'Background Color' => $slider->background_color,
                'Text Color' => $slider->text_color,
            ];
        });

        $filename = 'slider_analytics_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $this->notify('success', "Analytics exported successfully as {$filename}");
    }

    protected function refreshData(): void
    {
        // Clear any cached data
        cache()->forget('slider_analytics_data');

        $this->notify('success', 'Analytics data refreshed successfully');
    }

    public function getTitle(): string
    {
        return 'Slider Analytics';
    }

    public function getHeading(): string
    {
        return 'Slider Performance Analytics';
    }

    public function getSubheading(): string
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();

        return "Analytics for period: {$startDate->format('M d, Y')} - {$endDate->format('M d, Y')}";
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'slider-analytics';
    }
}
