<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Services\RecommendationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

final class RecommendationSystemManagement extends Page
{
    // protected static $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static UnitEnum|string|null $navigationGroup = 'Recommendation System';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.recommendation-system-management';

    protected static ?string $title = 'Recommendation System Management';

    public function clearCache(): void
    {
        try {
            $service = app(RecommendationService::class);
            $service->clearCache();
            
            Notification::make()
                ->title(__('translations.cache_cleared_successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('translations.cache_clear_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function optimizeSystem(): void
    {
        try {
            $service = app(RecommendationService::class);
            $service->optimizeRecommendations();
            
            Notification::make()
                ->title(__('translations.system_optimized_successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('translations.system_optimization_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('clear_cache')
                ->label(__('translations.clear_cache'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action('clearCache'),

            Action::make('optimize_system')
                ->label(__('translations.optimize_system'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->action('optimizeSystem'),
        ];
    }

    public function getSystemStats(): array
    {
        return [
            'total_blocks' => RecommendationBlock::count(),
            'active_blocks' => RecommendationBlock::active()->count(),
            'total_configs' => RecommendationConfig::count(),
            'active_configs' => RecommendationConfig::active()->count(),
            'cache_entries' => DB::table('recommendation_cache')->count(),
            'user_behaviors' => DB::table('user_behaviors')->count(),
            'product_similarities' => DB::table('product_similarities')->count(),
            'user_interactions' => DB::table('user_product_interactions')->count(),
        ];
    }

    public function getBlockPerformance(): array
    {
        return RecommendationBlock::with('analytics')
            ->get()
            ->map(function ($block) {
                $analytics = $block->analytics()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->get();

                return [
                    'name' => $block->name,
                    'title' => $block->title,
                    'is_active' => $block->is_active,
                    'total_requests' => $analytics->sum('hit_count'),
                    'avg_ctr' => $analytics->avg('ctr') ?? 0,
                    'avg_conversion' => $analytics->avg('conversion_rate') ?? 0,
                ];
            })
            ->toArray();
    }
}
