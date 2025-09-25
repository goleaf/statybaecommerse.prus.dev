<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources\NewsComments;

use App\Filament\Resources\NewsComments\Pages\CreateNewsComment;
use UnitEnum;
use BackedEnum;
use App\Filament\Resources\NewsComments\Pages\EditNewsComment;
use App\Filament\Resources\NewsComments\Pages\ListNewsComments;
use App\Filament\Resources\NewsComments\Schemas\NewsCommentForm;
use App\Filament\Resources\NewsComments\Tables\NewsCommentsTable;
use App\Models\NewsComment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsCommentResource extends Resource
{
    protected static ?string $model = NewsComment::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return NewsCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsCommentsTable::configure($table);
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
            'index' => ListNewsComments::route('/'),
            'create' => CreateNewsComment::route('/create'),
            'edit' => EditNewsComment::route('/{record}/edit'),
        ];
    }
}
