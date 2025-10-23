<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ProductSimilarity;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\RecommendationConfig;
use App\Models\UserBehavior;
use App\Models\UserProductInteraction;
use App\Services\RecommendationService;
use BackedEnum;
use UnitEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;
use UnitEnum;

final class RecommendationSystemManagement extends Page
{
    protected static ?string $title = 'Recommendation System Management';

    protected static ?string $navigationLabel = 'Recommendation System';

    protected static ?string $slug = 'recommendation-system-management';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 40;

    /**
     * @var string|BackedEnum|null
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';

    protected string $view = 'filament.pages.recommendation-system-management';

    public function mount(): void
    {
        // Prime computed data to avoid lazy-loading issues during initial render.
        $this->getSystemStats();
        $this->getBlockPerformance();
    }

    public function clearCache(): void
    {
        try {
            $this->recommendationService()->clearCache();

            Notification::make()
                ->title(__('translations.cache_cleared_successfully'))
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Log::warning('Failed to clear recommendation cache', ['error' => $exception->getMessage()]);

            Notification::make()
                ->title(__('translations.cache_clear_failed'))
                ->danger()
                ->body($exception->getMessage())
                ->send();
        }
    }

    public function optimizeSystem(): void
    {
        try {
            $this->recommendationService()->optimizeRecommendations();

            Notification::make()
                ->title(__('translations.system_optimized_successfully'))
                ->success()
                ->send();

            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            Log::warning('Failed to optimize recommendation system', ['error' => $exception->getMessage()]);

            Notification::make()
                ->title(__('translations.system_optimization_failed'))
                ->danger()
                ->body($exception->getMessage())
                ->send();
        }
    }

    /**
     * @return array<string, int>
     */
    public function getSystemStats(): array
    {
        return [
            'total_blocks'         => $this->countSafely(fn (): int => RecommendationBlock::query()->count()),
            'active_blocks'        => $this->countSafely(fn (): int => RecommendationBlock::query()->where('is_active', true)->count()),
            'total_configs'        => $this->countSafely(fn (): int => RecommendationConfig::query()->count()),
            'active_configs'       => $this->countSafely(fn (): int => RecommendationConfig::query()->where('is_active', true)->count()),
            'cache_entries'        => $this->countSafely(fn (): int => RecommendationCache::query()->count()),
            'user_behaviors'       => $this->countModelRecords(UserBehavior::class),
            'product_similarities' => $this->countModelRecords(ProductSimilarity::class),
            'user_interactions'    => $this->countModelRecords(UserProductInteraction::class),
        ];
    }

    /**
     * @return array<int, array{name: string, title: string|null, is_active: bool, total_requests: int, avg_ctr: float, avg_conversion: float}>
     */
    public function getBlockPerformance(): array
    {
        try {
            /** @var Collection<int, RecommendationBlock> $blocks */
            $blocks = RecommendationBlock::query()
                ->select(['id', 'name', 'title', 'is_active', 'sort_order'])
                ->orderBy('sort_order')
                ->get();
        } catch (Throwable $exception) {
            Log::warning('Failed to load recommendation blocks', ['error' => $exception->getMessage()]);

            return [];
        }

        if ($blocks->isEmpty()) {
            return [];
        }

        $analytics = $this->resolveAnalyticsMetrics($blocks->pluck('id')->all());

        return $blocks
            ->map(function (RecommendationBlock $block) use ($analytics): array {
                $metrics = $analytics[$block->id] ?? [
                    'total_requests' => 0,
                    'avg_ctr'        => 0.0,
                    'avg_conversion' => 0.0,
                ];

                return [
                    'name'           => $block->name,
                    'title'          => $block->title,
                    'is_active'      => (bool) $block->is_active,
                    'total_requests' => $metrics['total_requests'],
                    'avg_ctr'        => $metrics['avg_ctr'],
                    'avg_conversion' => $metrics['avg_conversion'],
                ];
            })
            ->values()
            ->all();
    }

    private function recommendationService(): RecommendationService
    {
        return app(RecommendationService::class);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param class-string<TModel> $modelClass
     */
    private function countModelRecords(string $modelClass): int
    {
        if (! class_exists($modelClass)) {
            return 0;
        }

        try {
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            return (int) $modelClass::query()->count();
        } catch (Throwable $exception) {
            Log::debug('Failed to count model records', ['model' => $modelClass, 'error' => $exception->getMessage()]);

            return 0;
        }
    }

    private function countSafely(callable $callback): int
    {
        try {
            return (int) $callback();
        } catch (Throwable $exception) {
            Log::debug('Failed to compute recommendation metric', ['error' => $exception->getMessage()]);

            return 0;
        }
    }

    /**
     * @param  array<int>                                                                    $blockIds
     * @return array<int, array{total_requests: int, avg_ctr: float, avg_conversion: float}>
     */
    private function resolveAnalyticsMetrics(array $blockIds): array
    {
        if ($blockIds === []) {
            return [];
        }

        try {
            /** @var Collection<int, RecommendationAnalytics> $analytics */
            $analytics = RecommendationAnalytics::query()
                ->whereIn('block_id', $blockIds)
                ->get();
        } catch (Throwable $exception) {
            Log::debug('Failed to load recommendation analytics', ['error' => $exception->getMessage()]);

            return [];
        }

        if ($analytics->isEmpty()) {
            return [];
        }

        return $analytics
            ->groupBy('block_id')
            ->map(function (Collection $records): array {
                $totalRequests = (int) $records->sum(function (RecommendationAnalytics $record): int {
                    $requests = data_get($record->metrics, 'requests');

                    if (is_numeric($requests)) {
                        return (int) $requests;
                    }

                    return 1;
                });

                $avgCtr = (float) $records->avg(
                    fn (RecommendationAnalytics $record): float => (float) ($record->ctr ?? 0)
                );

                $avgConversion = (float) $records->avg(
                    fn (RecommendationAnalytics $record): float => (float) ($record->conversion_rate ?? 0)
                );

                return [
                    'total_requests' => $totalRequests,
                    'avg_ctr'        => $avgCtr,
                    'avg_conversion' => $avgConversion,
                ];
            })
            ->all();
    }
}
