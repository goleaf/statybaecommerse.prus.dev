<?php

namespace App\Filament\Resources\NewsComments;
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
class NewsCommentResource extends Resource
{
    protected static ?string $model = NewsComment::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return NewsCommentForm::configure($schema);
    }
    public static function table(Table $table): Table
        return NewsCommentsTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListNewsComments::route('/'),
            'create' => CreateNewsComment::route('/create'),
            'edit' => EditNewsComment::route('/{record}/edit'),
}
