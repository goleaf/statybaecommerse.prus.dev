<?php

declare(strict_types=1);

namespace App\Filament\Resources\News;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Filament\Resources\News\Pages\ViewNews;
use App\Filament\Resources\News\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\News\Schemas\NewsForm;
use App\Filament\Resources\News\Schemas\NewsInfolist;
use App\Filament\Resources\News\Tables\NewsTable;
use App\Models\News;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use BackedEnum;
use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class NewsResource extends BaseResource
{
    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordRouteKeyName = 'id';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::Content->label();
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.news.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.news.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.news.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return NewsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NewsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsTable::configure($table);
    }

    /**
     * @param array<mixed> $parameters
     */
    public static function getUrl(
        ?string $name = null,
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false
    ): string {
        if (($parameters['record'] ?? null) instanceof News) {
            $parameters['record'] = (string) $parameters['record']->getKey();
        }

        return parent::getUrl(
            $name,
            $parameters,
            $isAbsolute,
            $panel,
            $tenant,
            $shouldGuessMissingParameters,
        );
    }

    public static function resolveRecordRouteBinding(int|string $key, ?Closure $modifyQuery = null): ?Model
    {
        $query = self::getRecordRouteBindingEloquentQuery();

        if ($modifyQuery) {
            $query = $modifyQuery($query) ?? $query;
        }

        $record = (clone $query)->whereKey($key)->first();

        if ($record instanceof Model) {
            return $record;
        }

        if (! is_string($key) || $key === '') {
            return null;
        }

        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $record = (clone $query)
            ->whereHas('translations', static function (Builder $translationQuery) use ($key, $locale): void {
                $translationQuery
                    ->where('locale', $locale)
                    ->where('slug', $key);
            })
            ->first();

        if ($record instanceof Model) {
            return $record;
        }

        if (is_string($fallbackLocale) && $fallbackLocale !== '' && $fallbackLocale !== $locale) {
            $record = (clone $query)
                ->whereHas('translations', static function (Builder $translationQuery) use ($key, $fallbackLocale): void {
                    $translationQuery
                        ->where('locale', $fallbackLocale)
                        ->where('slug', $key);
                })
                ->first();

            if ($record instanceof Model) {
                return $record;
            }
        }

        return (clone $query)
            ->whereHas('translations', static function (Builder $translationQuery) use ($key): void {
                $translationQuery->where('slug', $key);
            })
            ->first();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
                PublishedScope::class,
                VisibleScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'view'   => ViewNews::route('/{record}'),
            'edit'   => EditNews::route('/{record}/edit'),
        ];
    }
}
