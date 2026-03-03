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
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
