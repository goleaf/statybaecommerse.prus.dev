<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsComments;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\NewsComments\Pages\CreateNewsComment;
use App\Filament\Resources\NewsComments\Pages\EditNewsComment;
use App\Filament\Resources\NewsComments\Pages\ListNewsComments;
use App\Filament\Resources\NewsComments\Schemas\NewsCommentForm;
use App\Filament\Resources\NewsComments\Tables\NewsCommentsTable;
use App\Models\NewsComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class NewsCommentResource extends Resource
{
    use HasNav;

    protected static ?string $model = NewsComment::class;

    /** @var string|\BackedEnum|null */
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return NewsCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return NewsCommentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
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