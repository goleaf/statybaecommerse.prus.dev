<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Widgets;

use App\Enums\ModerationState;
use App\Filament\Support\InteractsWithDateFilter;
use App\Models\News;
use Carbon\CarbonImmutable;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Cache;

final class NewsPerformanceChart extends AdvancedChartWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Publishing cadence';

    protected static string $color = 'info';

    public ?string $filter = 'quarter';

    protected function getFilters(): ?array
    {
        return [
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
        ];
    }

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $diff = $from->diffInDays($to);
        $granularityMethod = $diff > 180 ? 'perMonth' : 'perDay';

        /** @var array{labels: array<int, string>, published: array<int, int>, drafted: array<int, int>} $chart */
        $chart = Cache::remember(
            sprintf('filament:widgets:news-performance:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(10),
            static function () use ($from, $to, $granularityMethod, $diff): array {
                $publishedTrend = Trend::query(
                    News::withoutGlobalScopes()
                        ->where('moderation_state', ModerationState::Published->value)
                        ->whereBetween('created_at', [$from, $to])
                )
                    ->between($from, $to)
                    ->{$granularityMethod}()
                    ->count();

                $draftTrend = Trend::query(
                    News::withoutGlobalScopes()
                        ->where('moderation_state', ModerationState::Draft->value)
                        ->whereBetween('created_at', [$from, $to])
                )
                    ->between($from, $to)
                    ->{$granularityMethod}()
                    ->count();

                $labels = [];
                $published = [];
                $drafts = [];

                foreach ($publishedTrend as $value) {
                    $labels[] = $diff > 180
                        ? CarbonImmutable::parse($value->date)->isoFormat('MMM YYYY')
                        : CarbonImmutable::parse($value->date)->isoFormat('MMM D');
                    $published[] = (int) $value->aggregate;
                }

                $draftIndex = 0;
                foreach ($draftTrend as $value) {
                    $drafts[$draftIndex++] = (int) $value->aggregate;
                }

                $drafts = array_values($drafts);

                if (count($drafts) < count($published)) {
                    $drafts = array_pad($drafts, count($published), 0);
                }

                return [
                    'labels'    => $labels,
                    'published' => $published,
                    'drafted'   => $drafts,
                ];
            }
        );

        return [
            'datasets' => [
                [
                    'label'           => __('Published'),
                    'data'            => $chart['published'],
                    'type'            => 'bar',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.3)',
                    'borderColor'     => '#3b82f6',
                    'borderWidth'     => 1,
                ],
                [
                    'label'           => __('Drafted'),
                    'data'            => $chart['drafted'],
                    'type'            => 'line',
                    'borderColor'     => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.15)',
                    'borderWidth'     => 2,
                    'tension'         => 0.3,
                ],
            ],
            'labels' => $chart['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
