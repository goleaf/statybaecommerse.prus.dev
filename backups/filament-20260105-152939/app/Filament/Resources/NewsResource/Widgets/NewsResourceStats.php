<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Widgets;

use App\Enums\ModerationState;
use App\Filament\Support\InteractsWithDateFilter;
use App\Models\News;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

final class NewsResourceStats extends BaseWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Editorial pipeline';

    protected static ?string $badge = 'Newsroom';

    protected static ?string $badgeColor = 'info';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today'   => __('Today'),
            'week'    => __('Last week'),
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
        ];
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);

        /** @var array{
         *     total: int,
         *     published: int,
         *     drafts: int,
         *     featured: int,
         *     newArticles: int
         * } $metrics
         */
        $metrics = Cache::remember(
            sprintf('filament:widgets:news-stats:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(10),
            static function () use ($from, $to): array {
                $baseQuery = News::withoutGlobalScopes();

                $total = (clone $baseQuery)->count();
                $published = (clone $baseQuery)
                    ->where('moderation_state', ModerationState::Published->value)
                    ->count();
                $drafts = (clone $baseQuery)
                    ->where('moderation_state', ModerationState::Draft->value)
                    ->count();
                $featured = (clone $baseQuery)
                    ->where('is_featured', true)
                    ->count();
                $newArticles = News::withoutGlobalScopes()
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

                return [
                    'total'       => (int) $total,
                    'published'   => (int) $published,
                    'drafts'      => (int) $drafts,
                    'featured'    => (int) $featured,
                    'newArticles' => (int) $newArticles,
                ];
            }
        );

        return [
            Stat::make(__('Total articles'), Number::format($metrics['total']))
                ->icon('heroicon-o-newspaper')
                ->iconBackgroundColor('primary')
                ->description(__('All articles, any state')),
            Stat::make(__('Published'), Number::format($metrics['published']))
                ->icon('heroicon-o-check-badge')
                ->iconBackgroundColor('success')
                ->description(__('Live on site')),
            Stat::make(__('Drafts'), Number::format($metrics['drafts']))
                ->icon('heroicon-o-pencil-square')
                ->iconBackgroundColor('warning')
                ->description(__('Awaiting review')),
            Stat::make(__('Featured'), Number::format($metrics['featured']))
                ->icon('heroicon-o-star')
                ->iconBackgroundColor('info')
                ->description(__('Marked as featured')),
            Stat::make(__('New this period'), Number::format($metrics['newArticles']))
                ->icon('heroicon-o-bolt')
                ->iconBackgroundColor('secondary')
                ->description(__('Created during the selected period')),
        ];
    }
}
