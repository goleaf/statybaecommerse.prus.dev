<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsComments;

use App\Filament\Resources\NewsComments\Pages\CreateNewsComment;
use App\Filament\Resources\NewsComments\Pages\EditNewsComment;
use App\Filament\Resources\NewsComments\Pages\ListNewsComments;
use App\Filament\Resources\NewsComments\Schemas\NewsCommentForm;
use App\Filament\Resources\NewsComments\Tables\NewsCommentsTable;
use App\Models\NewsComment;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\VisibleScope;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsCommentResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while matching the base resource type.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return NewsCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return NewsCommentsTable::configure($table);
    }

    /**
     * Provide an unscoped builder so moderation views can inspect every NewsComment record.
     *
     * @return Builder<NewsComment>
     */
    public static function getEloquentQuery(): Builder
    {
        // Remove the public-facing visibility scopes so administrators can audit every comment record.
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            ApprovedScope::class,
            VisibleScope::class,
            'active_flag',
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNewsComments::route('/'),
            'create' => CreateNewsComment::route('/create'),
            'edit'   => EditNewsComment::route('/{record}/edit'),
        ];
    }
}
