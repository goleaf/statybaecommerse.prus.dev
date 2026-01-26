<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
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

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-server-stack';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.admin); // Keep cache tooling aligned with the broader system utilities group.
    }

    protected static ?string $slug = 'cache-maintenance';

    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.cache-maintenance';

    public ?string $cacheKey = null;

    /**
     * @var array<int, string>
     */
    public array $cacheTags = [];

    /**
     * @var array<int, array{label: string, description?: string, url: string}>
     */
    public array $cachePolicyLinks = [];

    public function mount(): void
    {
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

    public function form(Form $schema): Form
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        // Embrace the Filament v4 return contract so downstream tooling can rely on a `Schema` instance for hydration.
        return $schema
            ->schema([
                Section::make(__('admin.cache_maintenance.targeted_cache_operations'))
                    ->description(__('admin.cache_maintenance.targeted_cache_operations_description'))
                    ->schema([
                        Forms\Components\TextInput::make('cacheKey')
                            ->label(__('admin.cache_maintenance.cache_key'))
                            ->helperText(__('admin.cache_maintenance.cache_key_help'))
                            ->maxLength(255),
                        Forms\Components\TagsInput::make('cacheTags')
                            ->label(__('admin.cache_maintenance.cache_tags'))
                            ->helperText(__('admin.cache_maintenance.cache_tags_help'))
                            ->placeholder(__('admin.cache_maintenance.cache_tags_placeholder')),
                    ])
                    ->columns(1),
            ]);
    }

    public function getTitle(): string
    {
        return __('admin.cache_maintenance.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.cache_maintenance.navigation_label');
    }

    protected function getActions(): array
    {
        return [
            Action::make('forgetCacheKey')
                ->label(__('admin.cache_maintenance.forget_cache_key'))
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function (): void {
                    $key = $this->cacheKey;
                    if ($key === null || $key === '') {
                        // Surface the validation feedback via the Filament notification pipeline
                        // so the action remains compatible with Livewire testing hooks.
                        Notification::make()
                            ->title(__('admin.cache_maintenance.cache_key_required_title'))
                            ->body(__('admin.cache_maintenance.cache_key_required_body'))
                            ->danger()
                            ->send();

                        return;
                    }

                    Cache::forget($key);
                    Notification::make()
                        ->title(__('admin.cache_maintenance.cache_key_cleared_title'))
                        ->body(__('admin.cache_maintenance.cache_key_cleared_body', ['key' => $key]))
                        ->success()
                        ->send();
                }),
            Action::make('flushCacheTags')
                ->label(__('admin.cache_maintenance.flush_cache_tags'))
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
                            ->title(__('admin.cache_maintenance.cache_tags_required_title'))
                            ->body(__('admin.cache_maintenance.cache_tags_required_body'))
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        Cache::tags($tags)->flush();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title(__('admin.cache_maintenance.tagged_cache_unavailable_title'))
                            ->body(__('admin.cache_maintenance.tagged_cache_unavailable_body', [
                                'message' => $exception->getMessage(),
                            ]))
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title(__('admin.cache_maintenance.tagged_cache_flushed_title'))
                        ->body(__('admin.cache_maintenance.tagged_cache_flushed_body', [
                            'tags' => implode(', ', $tags),
                        ]))
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<int, array{label: string, description?: string, url: string}>
     */
    private function buildCachePolicyLinks(): array
    {
        $links = [];

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => __('admin.cache_maintenance.cache_policy_overview'),
                'description' => __('admin.cache_maintenance.cache_policy_overview_description'),
                'url'         => route('filament.admin.resources.documents.index'),
            ];
        }

        if (Route::has('filament.admin.resources.documents.index')) {
            $links[] = [
                'label'       => __('admin.cache_maintenance.cache_policy_checklist'),
                'description' => __('admin.cache_maintenance.cache_policy_checklist_description'),
                'url'         => route('filament.admin.resources.documents.index', [
                    'tableSearch' => 'CachePolicy',
                ]),
            ];
        }

        return $links;
    }
}
