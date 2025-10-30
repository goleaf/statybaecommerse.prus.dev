<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Shared\ComponentPerformanceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Throwable;

final class CacheMaintenance extends Page
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while
     * documenting the accepted union type for downstream tooling.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-server-stack';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-server-stack';
    }
    public static function getNavigationGroup(): BackedEnum|string|null
    {
        return 'System'; // Keep cache tooling aligned with the broader system utilities group.
    }

    protected static ?string $title = 'Cache Maintenance';

    protected static ?string $slug = 'cache-maintenance';

    protected static ?string $navigationLabel = 'Cache Maintenance';

    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.cache-maintenance';

    public ?string $cacheKey = null;

    /**
     * @var array<int, string>
     */
    public array $cacheTags = [];

    /**
     * @var array<string, int|string|null>
     */
    public array $cachePerformanceSummary = [];

    /**
     * @var array<int, array{label: string, description?: string, url: string}>
     */
    public array $cachePolicyLinks = [];

    public function mount(): void
    {
        $this->refreshCacheMetrics();
        $this->cachePolicyLinks = $this->buildCachePolicyLinks();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if ($user === null) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'administrator'])) {
            return true;
        }

        // Provide a graceful fallback for legacy admin toggles when role data
        // has not been seeded alongside the core user accounts.
        return (bool) ($user->is_admin ?? false);
    }

    public function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        // Embrace the Filament v4 return contract so downstream tooling can rely on a `Schema` instance for hydration.
        return $schema
            ->schema([
                Section::make('Targeted Cache Operations')
                    ->description('Use scoped operations before clearing broad cache areas to follow CachePolicy guidance.')
                    ->schema([
                        Forms\Components\TextInput::make('cacheKey')
                            ->label('Cache key')
                            ->helperText('Provide the exact cache key to invalidate. Leave empty to skip this operation.')
                            ->maxLength(255),
                        Forms\Components\TagsInput::make('cacheTags')
                            ->label('Cache tags')
                            ->helperText('Specify one or more cache tags to flush together. Tags are matched exactly.')
                            ->placeholder('discounts'),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('forgetCacheKey')
                ->label('Forget Cache Key')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function (): void {
                    $key = $this->cacheKey;
                    if ($key === null || $key === '') {
                        // Surface the validation feedback via the Filament notification pipeline
                        // so the action remains compatible with Livewire testing hooks.
                        Notification::make()
                            ->title('Cache key required')
                            ->body('Please provide a cache key to forget.')
                            ->danger()
                            ->send();

                        return;
                    }

                    Cache::forget($key);
                    Notification::make()
                        ->title('Cache key cleared')
                        ->body("The cache entry '{$key}' was removed successfully.")
                        ->success()
                        ->send();
                    $this->refreshCacheMetrics();
                }),
            Action::make('flushCacheTags')
                ->label('Flush Cache Tags')
                ->icon('heroicon-o-tag')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $tags = array_values(array_filter(array_map(
                        static fn (mixed $value): ?string => is_string($value) ? trim($value) : null,
                        $this->cacheTags
                    )));

                    if (empty($tags)) {
                        Notification::make()
                            ->title('Cache tags required')
                            ->body('Add at least one cache tag before flushing tagged cache entries.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        Cache::tags($tags)->flush();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Tagged cache unavailable')
                            ->body('The current cache driver does not support tag operations. ' . $exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Tagged cache flushed')
                        ->body('The following tags were cleared: ' . implode(', ', $tags))
                        ->success()
                        ->send();
                    $this->refreshCacheMetrics();
                }),
        ];
    }

    private function refreshCacheMetrics(): void
    {
        $service = app(ComponentPerformanceService::class);
        $summary = $service->getPerformanceReport();
        $this->cachePerformanceSummary = Arr::only($summary, [
            'total_components',
            'total_renders',
            'avg_render_time',
            'slowest_component',
            'slowest_time',
            'most_used_component',
            'most_used_count',
            'performance_score',
        ]);
    }

    /**
     * @return array<int, array{label: string, description?: string, url: string}>
     */
    private function buildCachePolicyLinks(): array
    {
        $links = [];

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => 'CachePolicy Overview',
                'description' => 'Review the CachePolicy documentation before running destructive cache operations.',
                'url'         => route('filament.admin.resources.documents.index'),
            ];
        }

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => 'CachePolicy Checklist',
                'description' => 'Search for "CachePolicy" within the Documents module for step-by-step guidance.',
                'url'         => route('filament.admin.resources.documents.index', [
                    'tableSearch' => 'CachePolicy',
                ]),
            ];
        }

        return $links;
    }
}
